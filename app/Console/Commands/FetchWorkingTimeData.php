<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class FetchWorkingTimeData extends Command
{
    protected $signature = 'app:fetch-working-time-data {calendar-from-date? : Date in format YYYY-MM-DD}';
    protected $description = 'Fetch calendar data from the specified URL';

    /**
     * @throws GuzzleException
     */
    public function handle()
    {
        $currentDate = $this->argument('calendar-from-date') ?? now()->format('Y-m-d');

        try {
            $response = (new Client())->request('GET', 'http://192.168.56.117/', [
                'query' => [
                    'calendar-from-date' => $currentDate,
                    'filter' => 'Tất cả',
                    'export-data' => 'true'
                ]
            ]);

            if ($response->getStatusCode() == 200) {
                $this->info("Call api to FetchWorkingTimeData: status success");
            }

        } catch (\Exception $exception) {
            $this->error($exception);
        }
    }
}
