<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class WebController extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {
        return view('welcome');
    }

    public function tool(Request $request)
    {
        // [validate]
        if (!$request->has('calendar-from-date') || !$request->has('filter')) {
            return Redirect::route('home', [
                'calendar-from-date' => Carbon::today()->toDateString(),
                'filter' => 'Tất cả'
            ]);
        }

        // [validate]
        $calendarFromDate = Carbon::createFromFormat('Y-m-d', $request->input('calendar-from-date'));
        if ($calendarFromDate->isFuture()) {
            return redirect()->route('home', [
                'calendar-from-date' => Carbon::today()->toDateString(),
                'filter' => 'Tất cả'
            ]);
        }

        // [validate]
        try {
            $request->validate(['calendar-from-date' => 'required|date|date_format:Y-m-d',]);
        } catch (ValidationException $exception) {
            abort(404);
        }

        $fromDate = $request->query('calendar-from-date', Carbon::today()->toDateString());
        $defaultTime = new DateTime($fromDate . ' 08:00:00');

        // [validate]
        $dayOfWeek = date('N', strtotime($fromDate));
        if ($dayOfWeek == 6 || $dayOfWeek == 7) {
            abort(404);
        }

        $dataStaff = $staffCodeComplain = [];

        if ($token = $this->getAccessToken()) {

            // Get list Position
            $positions = $this->listPosition();
            $timeKeepings = $this->listTimeKeepings($fromDate);

            // Get data staff OFF
            $dataOff = $this->getDataStaffOff($token, $fromDate);
            foreach ($dataOff as $itemOff) {
                $userObjId = $itemOff['userObjId'] ?? [];
                $staffPosition = $this->getPositionByStaffCode($userObjId['staffCode'] ?? '', $positions);
                $staffCodeComplain[] = $userObjId['staffCode'] ?? '';
                $isViolateCreatedAt = !empty($itemOff['createdAt']) && (new DateTime($itemOff['createdAt'])) > $defaultTime;

                if ($staffPosition === 'Học việc') {
                    $isViolateCreatedAt = false;
                }

                $dataStaff[] = [
                    'staffCode' => $userObjId['staffCode'] ?? '',
                    'staffName' => $userObjId['name'] ?? '',
                    'staffPosition' => $staffPosition,
                    'requestType' => 'Nghỉ phép',
                    'fromDate' => $itemOff['fromDate'] ?? '',
                    'endDate' => $itemOff['endDate'] ?? '',
                    'requestCreatedAt' => $itemOff['createdAt'] ?? '',
                    'isViolateCreatedAt' => $isViolateCreatedAt,
                    'requestStatus' => $itemOff['statusApproval'] ?? '',
                    'displayStatus' => $this->getDisplayApproveStatus($itemOff['statusApproval'] ?? ''),
                    'requestReason' => $itemOff['reason'] ?? '',
                    'timeCheckIn' => null,
                    'isViolatetimeCheckIn' => false,
                ];
            }

            // Get data staff WFH
            $dataWFH = $this->getDataStaffWFH($token, $fromDate);
            foreach ($dataWFH as $itemWFH) {
                $timeKeepingsStaff = collect($timeKeepings[$itemWFH['userStaffCode']]['timeKeepings'] ?? [])->mapWithKeys(function ($item) {
                    return [$item['dateKeeping'] => $item];
                })->toArray();

                $staffCodeComplain[] = $itemWFH['userStaffCode'] ?? '';
                $staffPosition = $this->getPositionByStaffCode($itemWFH['userStaffCode'] ?? '', $positions);
                $isViolateCreatedAt = !empty($itemWFH['createdAt']) && (new DateTime($itemWFH['createdAt'])) > $defaultTime;
                $isViolatetimeCheckIn = empty($timeKeepingsStaff[$fromDate]['timeCheckIn']) || (new DateTime($timeKeepingsStaff[$fromDate]['timeCheckIn'])) > $defaultTime;

                if ($staffPosition === 'Học việc') {
                    $isViolateCreatedAt = false;
                    $isViolatetimeCheckIn = false;
                }

                $dataStaff[] = [
                    'staffCode' => $itemWFH['userStaffCode'] ?? '',
                    'staffName' => $itemWFH['username'] ?? '',
                    'staffPosition' => $staffPosition,
                    'requestType' => 'WFH',
                    'fromDate' => $itemWFH['fromDate'] ?? '',
                    'endDate' => $itemWFH['endDate'] ?? '',
                    'requestCreatedAt' => $itemWFH['createdAt'] ?? '',
                    'isViolateCreatedAt' => $isViolateCreatedAt,
                    'requestStatus' => $itemWFH['statusApproval'] ?? '',
                    'displayStatus' => $this->getDisplayApproveStatus($itemWFH['statusApproval'] ?? ''),
                    'requestReason' => $itemWFH['reason'] ?? '',
                    'timeCheckIn' => $timeKeepingsStaff[$fromDate]['timeCheckIn'] ?? null,
                    'isViolatetimeCheckIn' => $isViolatetimeCheckIn
                ];
            }

            // Get data staff empty / checkin
            $dataEmpty = $dataCheckIn = [];
            foreach ($timeKeepings as $code => $staff) {
                if (!in_array(intval($code), $staffCodeComplain)) {
                    $dateKeepingStaff = collect($staff['timeKeepings'] ?? [])->mapWithKeys(function ($item) {
                        return [$item['dateKeeping'] => $item];
                    })->toArray();
                    if (isset($dateKeepingStaff[$fromDate])) {
                        if (empty($dateKeepingStaff[$fromDate]['timeCheckIn'])) {
                            $isViolatetimeCheckIn = !($dateKeepingStaff[$fromDate]['symbolKeeping'] === 'L');

                            if ($staff['positionName'] === 'Học việc') {
                                $isViolatetimeCheckIn = false;
                            }

                            $dataEmpty[] = [
                                'staffCode' => $staff['staffCode'] ?? '',
                                'staffName' => $staff['name'] ?? '',
                                'staffPosition' => $staff['positionName'] ?? '',
                                'requestType' => '-',
                                'fromDate' => '',
                                'endDate' => '',
                                'requestCreatedAt' => '',
                                'isViolateCreatedAt' => false,
                                'requestStatus' => '',
                                'displayStatus' => '',
                                'requestReason' => '',
                                'timeCheckIn' => null,
                                'isViolatetimeCheckIn' => $isViolatetimeCheckIn
                            ];
                        } else {
                            $isViolatetimeCheckIn = (new DateTime($dateKeepingStaff[$fromDate]['timeCheckIn'])) > $defaultTime;

                            if ($staff['positionName'] === 'Học việc') {
                                $isViolatetimeCheckIn = false;
                            }

                            $dataCheckIn[] = [
                                'staffCode' => $staff['staffCode'] ?? '',
                                'staffName' => $staff['name'] ?? '',
                                'staffPosition' => $staff['positionName'] ?? '',
                                'requestType' => 'Bình thường',
                                'fromDate' => '',
                                'endDate' => '',
                                'requestCreatedAt' => '',
                                'isViolateCreatedAt' => false,
                                'requestStatus' => '',
                                'displayStatus' => '',
                                'requestReason' => '',
                                'timeCheckIn' => $dateKeepingStaff[$fromDate]['timeCheckIn'],
                                'isViolatetimeCheckIn' => $isViolatetimeCheckIn
                            ];
                        }
                    }
                }
            }
            $dataStaff = array_merge($dataStaff, $dataCheckIn, $dataEmpty);
        }

        $countPunished = $countNotPunished = 0;
        foreach ($dataStaff as $itemStaff) {
            if ($itemStaff['isViolatetimeCheckIn'] || $itemStaff['isViolateCreatedAt']) {
                $countPunished++;
            }

            if (!$itemStaff['isViolatetimeCheckIn'] && !$itemStaff['isViolateCreatedAt']) {
                $countNotPunished++;
            }
        }

        $isViolate = $request->query('filter', 'Tất cả');
        if ($isViolate !== 'Tất cả') {
            $dataStaff = collect($dataStaff)->filter(function ($item) use ($isViolate) {
                if ($isViolate === 'Có') {
                    return $item['isViolatetimeCheckIn'] || $item['isViolateCreatedAt'];
                } else {
                    return !$item['isViolatetimeCheckIn'] && !$item['isViolateCreatedAt'];
                }
            })->toArray();
        }

        return view('tool')->with([
            'dataStaff' => $dataStaff,
            'countPunished' => $countPunished,
            'countNotPunished' => $countNotPunished
        ]);
    }

    public function getDisplayApproveStatus($status): string
    {
        $approveStatus = $status;

        if ($status === 'APPROVED') {
            $approveStatus = 'Đã duyệt';
        } elseif ($status === 'PENDING') {
            $approveStatus = 'Chờ duyệt';
        }

        return $approveStatus;
    }

    public function toolOff(Request $request)
    {
        $dataOff = [];

        if ($token = $this->getAccessToken()) {
            $dataOff = $this->getDataStaffOff($token, $request->input('calendar-from-date'));
        }

        return view('Web.toolOff')->with(['dataOFF' => $dataOff]);
    }

    function encryptString($string, $key): string
    {
        $cipher_method = 'AES-256-CBC';
        $iv_length = openssl_cipher_iv_length($cipher_method);
        $iv = openssl_random_pseudo_bytes($iv_length);
        $encrypted = openssl_encrypt($string, $cipher_method, $key, OPENSSL_RAW_DATA, $iv);

        return base64_encode($iv . $encrypted);
    }

    function decryptString($encrypted_string, $key): bool|string
    {
        $cipher_method = 'AES-256-CBC';
        $iv_length = openssl_cipher_iv_length($cipher_method);
        $encrypted_string = base64_decode($encrypted_string);
        $iv = substr($encrypted_string, 0, $iv_length);
        $encrypted = substr($encrypted_string, $iv_length);

        return openssl_decrypt($encrypted, $cipher_method, $key, OPENSSL_RAW_DATA, $iv);
    }


    public function getAccessToken()
    {
        $accessToken = null;

        $response = (new Client())->post('https://api-create.runsystem.info/signIn', [
            'headers' => [
                'Content-Type' => "application/json",
                'Authorization' => 'Basic ZHhpbnRlcm5hbF9wbDpnb0R4QDIwMjE='
            ],
            'json' => [
                'username' => $this->decryptString(env('ACCOUNT_NAME', ''), env('ACCOUNT_SECRET_KEY', '')),
                'password' => $this->decryptString(env('ACCOUNT_PASSWORD', ''), env('ACCOUNT_SECRET_KEY', ''))
            ]
        ]);

        if (!empty($data = json_decode($response->getBody(), true) ?? [])) {
            $accessToken = $data['token'] ?? null;
        }

        return $accessToken;
    }

    public function getDataStaffWFH($token, $fromDate)
    {
        $result = [];

        if (!empty($fromDate)) {
            $responseGet = (new Client())->get($this->generateUrl($fromDate), [
                'headers' => [
                    'Content-Type' => "application/json",
                    'Authorization' => 'Basic ZHhpbnRlcm5hbF9wbDpnb0R4QDIwMjE=',
                    'x-access-token' => $token
                ]
            ]);

            if ($responseGet->getStatusCode() == 200) {
                $result = json_decode($responseGet->getBody(), true) ?? [];
                if ($result['data']) {
                    $result = $result['data']['items'] ?? [];
                }
            }
        }

        return $result;
    }

    public function getPositionByStaffCode($code, $positions)
    {
        if (count($positions) > 0 && array_key_exists($code, $positions)) {
            $userPositionObjId = $positions[$code]['userPositionObjId'] ?? [];

            return $userPositionObjId['positionName'];
        }

        return '';
    }

    public function listPosition(): array
    {
        $positions = [];

        $contentFilePos = Storage::disk('public')->get('position.json');
        if ($contentFilePos) {
            $positionData = json_decode($contentFilePos, true) ?? [];
            if (array_key_exists('data', $positionData)) {
                $positions = collect($positionData['data'] ?? [])->mapWithKeys(function ($item) {
                    return [$item['staffCode'] => $item];
                })->toArray();
            }
        }

        return $positions;
    }

    public function listTimeKeepings($fromDate): array
    {
        $yearMonth = DateTime::createFromFormat('Y-m-d', $fromDate)->format('Y-m');
        $pathFileTimeKeepings = '/' . $yearMonth . '/' . 'checkin-time.json';

        if (Storage::disk('public')->exists($pathFileTimeKeepings)) {
            if ($contentFile = Storage::disk('public')->get($pathFileTimeKeepings)) {
                return json_decode($contentFile, true) ?? [];
            }
        }

        return [];
    }

    public function getDataStaffOff($token, $fromDate): array
    {
        $listDeptID = ['6305f89d54fd8d0284bc8094', '6305f86a54fd8d0284bc7fe3', '60b60c1f988d9913c49b86d2'];
        $reportObjId = ['608285d10e83773bc64b271d', '608285d10e83773bc64b274e'];
        $result = [];
        $client = new Client();

        if (!empty($fromDate)) {
            $responseGet = $client->get($this->generateUrlOff($fromDate), [
                'headers' => [
                    'Content-Type' => "application/json",
                    'Authorization' => 'Basic ZHhpbnRlcm5hbF9wbDpnb0R4QDIwMjE=',
                    'x-access-token' => $token
                ]
            ]);

            if ($responseGet->getStatusCode() == 200) {
                $contentData = json_decode($responseGet->getBody(), true) ?? [];
                if (isset($contentData['data'])) {
                    foreach ($contentData['data']['items'] ?? [] as $item) {
                        $userApprovalObjId = $item['userApprovalObjId'] ?? [];
                        if (isset($userApprovalObjId['departmentObjId'])) {
                            if (in_array($userApprovalObjId['departmentObjId'], $listDeptID)) {
                                $result[] = $item;
                            }
                        } elseif (isset($item['reportObjId']) && in_array($item['reportObjId']['_id'], $reportObjId)) {
                            $result[] = $item;
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function generateUrl($fromDate)
    {
        $departmentObjId = '60b60c1f988d9913c49b86d2';
        $apiLink = 'https://api-create.runsystem.info/auth/staff-wfh/listByManager';
        $query = "endDate=$fromDate&fromDate=$fromDate&limit=300&status=All&statusApproval=%5Bobject%20Object%5D&toDate=1709225999999&departmentObjId=$departmentObjId";

        return $apiLink . '?' . $query;
    }

    public function generateUrlOff($date)
    {

        $apiLink = 'https://api-create.runsystem.info/auth/staff-attendance/personalStaffAttendance';
        $query = "endDate=$date&page=1&startDate=$date&status=All&limit=500";

        return $apiLink . '?' . $query;
    }
}
