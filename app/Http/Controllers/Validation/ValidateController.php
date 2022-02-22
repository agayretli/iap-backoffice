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
        $response = [];
        // Rate-limit error
        if (!(($receipt % 100) % 6)) {
            if (rand(0, 1)) {
                //todo
                //$response['message'] = 'RATE-LIMIT ERROR';
            }
        }

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
