<?php

namespace App\Console\Commands;

use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class GetDataTimeKeepingsStaff extends Command
{
    protected $signature = 'app:get-data-time-keepings-staff';
    protected $description = 'Command description';

    /**
     * @throws GuzzleException
     */
    public function handle(): bool
    {

        Log::error('[Command] get-data-time-keepings-staff: Running.');

        $date = now()->format('Y-m-d');
        $validator = Validator::make(['date' => $date], [
            'date' => 'date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            $this->error('Invalid date format. The date should be in the format: YYYY-MM-DD');
            return false;
        }

        $data = $this->listTimeKeepings($date);
        if (!empty($data)) {
            $pathFile = '/' . DateTime::createFromFormat('Y-m-d', $date)->format('Y-m') . '/' . 'checkin-time.json';
            if (Storage::disk('public')->put($pathFile, json_encode($data))) {
                $this->info(Storage::disk('public')->path($pathFile));

                Log::error('[Command] get-data-time-keepings-staff: Success.');
                return true;
            }
        }

        $this->error('Fail to get data from api tool-create: ' . $date);
        $this->error('Link api: ' . $this->generateTimeKeepings($date));
        $this->error('Token: ' . env('ACCOUNT_TOKEN', ''));

        Log::error('[Command] get-data-time-keepings-staff: Fail.');
        return false;
    }

    /**
     * @throws GuzzleException
     */
    public function listTimeKeepings($fromDate): array
    {
        $result = [];
        $client = new Client();

        if (!empty($fromDate)) {
            $responseGet = $client->get($this->generateTimeKeepings($fromDate), [
                'headers' => [
                    'Content-Type' => "application/json",
                    'Authorization' => 'Basic ZHhpbnRlcm5hbF9wbDpnb0R4QDIwMjE=',
                    'x-access-token' => env('ACCOUNT_TOKEN', '')
                ]
            ]);

            if ($responseGet->getStatusCode() == 200) {
                $timeData = json_decode($responseGet->getBody(), true) ?? [];
                if (array_key_exists('data', $timeData)) {
                    $result = collect($timeData['data']['items'] ?? [])->mapWithKeys(function ($item) {
                        return [$item['staffCode'] => $item];
                    })->toArray();
                }
            }
        }

        return $result;
    }

    public function generateTimeKeepings($date): string
    {
        $departmentObjId = '60b60c1f988d9913c49b86d2';
        $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        $closingMonth = $dateTime->format('mY');
        $apiLink = 'https://api-create.runsystem.info/auth/time-keepings/listByManager';
        $query = "branch&closingMonth=$closingMonth&departmentObjId=$departmentObjId&limit=100&name=&page=1&project=%5Bobject%20Object%5D&search=&sortKey=createdAt&sortOrder=-1&status";

        return $apiLink . '?' . $query;
    }
}
