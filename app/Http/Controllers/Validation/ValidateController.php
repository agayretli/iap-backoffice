<?php

namespace App\Http\Controllers\Validation;

use App\Http\Controllers\Controller;
use Carbon\Carbon;

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
        $response_body = $response->getBody();
        if (is_null($response_body) || empty($response_body)) {
            return $this->localeMock($receipt);
        }
        $body = json_decode($response_body, true);

        return $body;
    }

    public function localMock($receipt)
    {
        $response = [];
        // Rate-limit error
        /*if (!(($receipt % 100) % 6)) {
            if (rand(0, 1)) {
                //todo
                $response['message'] = 'RATE-LIMIT ERROR';
            }
        }*/

        //validate
        if ($receipt % 2) {
            $response['message'] = 'OK';
            $response['status'] = true;
            $date = Carbon::now()->addMonth();
            $response['expire-date'] = $date->setTimezone('America/Belize')->format('Y-m-d H:i:s');

            return $response;
        }
        $response['message'] = 'REJECT';
        $response['status'] = false;

        return $response;
    }
}
