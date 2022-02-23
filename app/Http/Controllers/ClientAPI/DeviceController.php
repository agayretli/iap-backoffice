<?php

namespace App\Http\Controllers\ClientAPI;

use App\Events\Renewed;
use App\Events\Started;
use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Device;
use App\Models\DeviceApp;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Validator;
use Webpatser\Uuid\Uuid;

class DeviceController extends Controller
{
    public function __construct()
    {
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'appId' => 'required',
            'language' => 'required',
            'operating_system' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => false, 'message' => 'Validator failed.'], 200);
        }

        $device_id = $request->uid;
        $app_id = $request->appId;
        $language = $request->language;
        $operating_system = $request->operating_system;

        if ($operating_system != 'android' && $operating_system != 'ios') {
            return response()->json(['result' => false, 'message' => 'Unexpected os.'], 200);
        }

        $client_token = Cache::remember('device_'.$device_id.'app_'.$app_id, 5, function () use ($device_id, $app_id, $language, $operating_system) {
            try {
                $device_app = DeviceApp::where(['device_id' => $device_id, 'app_id' => $app_id])->first();
                if ($device_app == null) {
                    $device = Device::find($device_id);
                    if ($device == null) {
                        $device = new Device();
                        $device->id = $device_id;
                        $device->save();
                    }

                    $app = App::find($app_id);
                    if ($app == null) {
                        $app = new App();
                        $app->id = $app_id;
                        $app->save();
                    }

                    $device_app = new DeviceApp();
                    $device_app->language = $language;
                    $device_app->operating_system = $operating_system;
                    $clientToken = Uuid::generate()->string;
                    $device_app->client_token = $clientToken;
                    $device_app->device_id = $device->id;
                    $device_app->app_id = $app->id;
                    $device_app->save();
                } else {
                    $clientToken = $device_app->client_token;
                }
            } catch (Exception $e) {
            }

            return $clientToken;
        });
        if (is_null($client_token)) {
            Cache::forget('device_'.$device_id.'app_'.$app_id);

            return response()->json(['result' => false, 'message' => 'Register failed'], 200);
        }

        return response()->json(['result' => true, 'message' => 'OK', 'client-token' => $client_token], 200);
    }

    public function purchase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_token' => 'required',
            'receipt' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['result' => false, 'message' => 'required parameters.'], 200);
        }

        $device_app = DeviceApp::where('client_token', $request->client_token)->first();
        if ($device_app == null) {
            return response()->json(['result' => false, 'message' => 'client_token does not match'], 200);
        }

        if ($device_app->receipt == $request->receipt) {
            return response()->json(['result' => false, 'message' => 'Duplicate request.'], 200);
        }

        //Validate Receipt
        if ($device_app->operating_system == 'android') {
            $response = app('App\Http\Controllers\Validation\ValidateController')->googleVerify($request->receipt);
        } else {
            $response = app('App\Http\Controllers\Validation\ValidateController')->iosVerify($request->receipt);
        }
        if ($response->getStatusCode() == 200 || $response->getStatusCode() == 201) {
            $response_body = $response->getBody();
            if (!(is_null($response_body) || empty($response_body))) {
                $body = json_decode($response_body, true);
                if (!$body['status']) {
                    return response()->json(['result' => false, 'message' => 'Purchase not validated.'], 200);
                }
                $device_app->subscription = $body['status'];
                $device_app->receipt = $request->receipt;
                $is_updated = false;
                if ($device_app->expire_date != null) {
                    $is_updated = true;
                }
                $device_app->expire_date = $body['expire_date'];
                if ($device_app->save()) {
                } else {
                    return response()->json(['result' => false, 'message' => 'Error on save.'], 200);
                }
                //Callbacks
                if ($is_updated) {
                    event(new Renewed($device_app));
                } else {
                    event(new Started($device_app));
                }

                return response()->json(['result' => true, 'message' => $body['message'], 'status' => $body['status'], 'expire-date' => $body['expire_date']], 200);
            }
        }
        //Rate-limit error
        //Save receipt and expire-date
        //Will be checked on worker
        $device_app->subscription = false;
        $device_app->receipt = $request->receipt;
        $date = Carbon::now();
        $device_app->expire_date = $date->setTimezone('America/Belize')->format('Y-m-d H:i:s');
        if ($device_app->save()) {
        } else {
            return response()->json(['result' => false, 'message' => 'Error on save.'], 200);
        }

        return response()->json(['result' => false, 'message' => 'Error on request, the receipt will be checked on worker.'], 200);
    }

    public function checkSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_token' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['result' => false, 'message' => 'required client_token.'], 200);
        }
        $response = DeviceApp::select('subscription')->where('client_token', $request->client_token)->first();
        if ($response == null) {
            return response()->json(['result' => false, 'message' => 'client_token does not match'], 200);
        }

        return response()->json(['result' => true, 'subscription' => $response->subscription], 200);
    }
}
