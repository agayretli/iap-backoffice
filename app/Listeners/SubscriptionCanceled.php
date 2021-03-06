<?php

namespace App\Listeners;

use App\Events\Canceled;

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
        app('App\Http\Controllers\Report\ReportController')->addRecord($event->device_app, 'canceled');

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
