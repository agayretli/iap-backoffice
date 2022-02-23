<?php

namespace App\Http\Controllers\Validation;

use App\Http\Controllers\Controller;

class ValidateController extends Controller
{
    public function __construct()
    {
    }

    public function googleVerify($receipt)
    {
        return $this->mock($receipt);
    }

    public function iosVerify($receipt)
    {
        return $this->mock($receipt);
    }

    public function mock($receipt)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', env('ENDPOINT_URL', 'http://localhost:8080').'/api/mock/verify',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                        'receipt' => $receipt,
                ]),
            ]
        );

        return $response;
    }
}
