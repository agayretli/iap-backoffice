<?php

namespace App\Listeners;

use App\Events\Canceled;
use App\Models\Report;

class SubscriptionCanceled
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
    public function handle(Canceled $event)
    {
        //Reporting
        $report = new Report();
        $report->device_id = $event->device_app->device_id;
        $report->app_id = $event->device_app->app_id;
        $report->subscription_status = 'canceled';
        $report->operating_system = $event->device_app->operating_system;
        $report->save();

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', env('ENDPOINT_URL', 'http://localhost:8080').'/api/subscription/canceled',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                        'appID' => $event->device_app->app_id,
                        'deviceID' => $event->device_app->device_id,
                        'event' => 'canceled',
                ]),
            ]
        );
    }
}
