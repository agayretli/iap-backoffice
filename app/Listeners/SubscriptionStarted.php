<?php

namespace App\Listeners;

use App\Events\Started;
use App\Models\Report;

class SubscriptionStarted
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(Started $event)
    {
        //Reporting
        $report = new Report();
        $report->device_id = $event->device_app->device_id;
        $report->app_id = $event->device_app->app_id;
        $report->subscription_status = 'started';
        $report->operating_system = $event->device_app->operating_system;
        $report->save();

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', env('ENDPOINT_URL', 'http://localhost:8080').'/api/subscription/started',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                        'appID' => $event->device_app->app_id,
                        'deviceID' => $event->device_app->device_id,
                        'event' => 'started',
                ]),
            ]
        );
    }
}
