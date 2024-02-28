<?php

namespace App\Http\Controllers;

use DateTime;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $formDate = $request->input('calendar-from-date', '');
        $defaultTime = new DateTime('08:00:00');
        $dataStaff = $staffCodeComplain = [];

        if ($token = $this->getAccessToken()) {

            // Get list Position
            $positions = $this->listPosition();
            $timeKeepings = $this->listTimeKeepings();

            // Get data staff OFF
            $dataOff = $this->getDataStaffOff($token, $request->input('calendar-from-date'), $request->input('calendar-from-date'));
            foreach ($dataOff as $itemOff) {
                $userObjId = $itemOff['userObjId'] ?? [];

                $staffCodeComplain[] = $userObjId['staffCode'] ?? '';
                $dataStaff[] = [
                    'staffCode' => $userObjId['staffCode'] ?? '',
                    'staffName' => $userObjId['name'] ?? '',
                    'staffPosition' => $this->getPositionByStaffCode($userObjId['staffCode'] ?? '', $positions),
                    'requestType' => 'Nghỉ phép',
                    'requestCreatedAt' => $itemOff['createdAt'] ?? '',
                    'isViolateCreatedAt' => !empty($itemOff['createdAt']) && (new DateTime($itemOff['createdAt'])) > $defaultTime,
                    'requestStatus' => $itemOff['statusApproval'] ?? '',
                    'displayStatus' => $this->getDisplayApproveStatus($itemOff['statusApproval'] ?? ''),
                    'requestReason' => $itemOff['reason'] ?? '',
                    'timeCheckIn' => null,
                    'isViolatetimeCheckIn' => false,
                ];
            }

            // Get data staff WFH
            $dataWFH = $this->getDataStaffWFH($token, $request->input('calendar-from-date'), $request->input('calendar-from-date'));
            foreach ($dataWFH as $itemWFH) {
                $timeKeepingsStaff = collect($timeKeepings[$itemWFH['userStaffCode']]['timeKeepings'] ?? [])->mapWithKeys(function ($item) {
                    return [$item['dateKeeping'] => $item];
                })->toArray();

                $staffCodeComplain[] = $itemWFH['userStaffCode'] ?? '';
                $dataStaff[] = [
                    'staffCode' => $itemWFH['userStaffCode'] ?? '',
                    'staffName' => $itemWFH['username'] ?? '',
                    'staffPosition' => $this->getPositionByStaffCode($itemWFH['userStaffCode'] ?? '', $positions),
                    'requestType' => 'WFH',
                    'requestCreatedAt' => $itemWFH['createdAt'] ?? '',
                    'isViolateCreatedAt' => !empty($itemWFH['createdAt']) && (new DateTime($itemWFH['createdAt'])) > $defaultTime,
                    'requestStatus' => $itemWFH['statusApproval'] ?? '',
                    'displayStatus' => $this->getDisplayApproveStatus($itemWFH['statusApproval'] ?? ''),
                    'requestReason' => $itemWFH['reason'] ?? '',
                    'timeCheckIn' => $timeKeepingsStaff[$formDate]['timeCheckInRaw'] ?? '',
                    'isViolatetimeCheckIn' => empty($timeKeepingsStaff[$formDate]['timeCheckInRaw']) || (new DateTime($timeKeepingsStaff[$formDate]['timeCheckInRaw'])) > $defaultTime
                ];
            }

            // Get data staff empty / checkin
            $dataEmpty = $dataCheckIn = [];
            foreach ($timeKeepings as $code => $staff) {
                if (!in_array(intval($code), $staffCodeComplain)) {
                    $dateKeepingStaff = collect($staff['timeKeepings'] ?? [])->mapWithKeys(function ($item) {
                        return [$item['dateKeeping'] => $item];
                    })->toArray();
                    if (isset($dateKeepingStaff[$formDate])) {
                        if (empty($dateKeepingStaff[$formDate]['timeCheckIn'])) {
                            $dataEmpty[] = [
                                'staffCode' => $staff['staffCode'] ?? '',
                                'staffName' => $staff['name'] ?? '',
                                'staffPosition' => $staff['positionName'] ?? '',
                                'requestType' => '-',
                                'requestCreatedAt' => '',
                                'isViolateCreatedAt' => false,
                                'requestStatus' => '',
                                'displayStatus' => '',
                                'requestReason' => '',
                                'timeCheckIn' => '',
                                'isViolatetimeCheckIn' => true
                            ];
                        } else {
                            $dataCheckIn[] = [
                                'staffCode' => $staff['staffCode'] ?? '',
                                'staffName' => $staff['name'] ?? '',
                                'staffPosition' => $staff['positionName'] ?? '',
                                'requestType' => 'Bình thường',
                                'requestCreatedAt' => '',
                                'isViolateCreatedAt' => false,
                                'requestStatus' => '',
                                'displayStatus' => '',
                                'requestReason' => '',
                                'timeCheckIn' => $dateKeepingStaff[$formDate]['timeCheckIn'],
                                'isViolatetimeCheckIn' => (new DateTime($dateKeepingStaff[$formDate]['timeCheckIn'])) > $defaultTime
                            ];
                        }
                    }
                }
            }
            $dataStaff = array_merge($dataStaff, $dataCheckIn, $dataEmpty);
        }

        return view('tool')->with(['dataStaff' => $dataStaff]);
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
            $dataOff = $this->getDataStaffOff($token, $request->input('calendar-from-date'), $request->input('calendar-to-date'));
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

    public function getDataStaffWFH($token, $fromDate, $endDate)
    {
        $result = [];

        if (!empty($fromDate) && !empty($endDate)) {
            $responseGet = (new Client())->get($this->generateUrl($fromDate, $endDate), [
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

    public function listTimeKeepings(): array
    {
        $positions = [];

        $contentFilePos = Storage::disk('public')->get('time.json');
        if ($contentFilePos) {
            $positionData = json_decode($contentFilePos, true) ?? [];
            if (array_key_exists('data', $positionData)) {
                $positions = collect($positionData['data']['items'] ?? [])->mapWithKeys(function ($item) {
                    return [$item['staffCode'] => $item];
                })->toArray();
            }
        }

        return $positions;
    }

    public function getDataStaffOff($token, $fromDate, $endDate): array
    {
        $listDeptID = ['6305f89d54fd8d0284bc8094', '6305f86a54fd8d0284bc7fe3', '60b60c1f988d9913c49b86d2'];
        $reportObjId = ['608285d10e83773bc64b271d', '608285d10e83773bc64b274e'];
        $result = [];
        $client = new Client();

        if (!empty($fromDate) && !empty($endDate)) {
            $responseGet = $client->get($this->generateUrlOff($fromDate, $endDate), [
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

    public function generateUrl($fromDate, $endDate)
    {
        $departmentObjId = '60b60c1f988d9913c49b86d2';
        $apiLink = 'https://api-create.runsystem.info/auth/staff-wfh/listByManager';
        $query = "endDate=$endDate&fromDate=$fromDate&limit=300&status=All&statusApproval=%5Bobject%20Object%5D&toDate=1709225999999&departmentObjId=$departmentObjId";

        return $apiLink . '?' . $query;
    }

    public function generateUrlOff($startDate, $endDate)
    {

        $apiLink = 'https://api-create.runsystem.info/auth/staff-attendance/personalStaffAttendance';
        $query = "endDate=$endDate&page=1&startDate=$startDate&status=All&limit=500";

        return $apiLink . '?' . $query;
    }
}
