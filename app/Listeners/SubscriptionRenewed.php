<?php

namespace App\Listeners;

use App\Events\Renewed;

class SubscriptionRenewed
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
    public function handle(Renewed $event)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', env('ENDPOINT_URL', 'http://localhost:8080').'/api/subscription/renewed',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                        'appID' => $event->device_app->app_id,
                        'deviceID' => $event->device_app->device_id,
                        'event' => 'renewed',
                ]),
            ]
        );
    }
}
