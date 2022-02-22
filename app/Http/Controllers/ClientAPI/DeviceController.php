<?php

namespace App\Http\Controllers\ClientAPI;

use App\Events\Renewed;
use App\Events\Started;
use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Device;
use App\Models\DeviceApp;
use Illuminate\Http\Request;
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

        if ($request->operating_system != 'android' && $request->operating_system != 'ios') {
            return response()->json(['result' => false, 'message' => 'Unexpected os.'], 200);
        }

        $device = Device::find($request->uid);

        if ($device == null) {
            $device = new Device();
            $device->id = $request->uid;
            if ($device->save()) {
            } else {
                return response()->json(['result' => false, 'message' => 'Device can not created.'], 200);
            }
        }

        $app = App::find($request->appId);

        if ($app == null) {
            $app = new App();
            $app->id = $request->appId;
            if ($app->save()) {
            } else {
                return response()->json(['result' => false, 'message' => 'App can not created.'], 200);
            }
        }

        $device_app = DeviceApp::where(['device_id' => $request->uid, 'app_id' => $request->appId])->first();

        $client_token = '';
        if ($device_app == null) {
            $device_app = new DeviceApp();
            $device_app->language = $request->language;
            $client_token = Uuid::generate()->string;
            $device_app->operating_system = $request->operating_system;
            $device_app->client_token = $client_token;
            $device_app->device_id = $device->id;
            $device_app->app_id = $app->id;

            if ($device_app->save()) {
            } else {
                return response()->json(['result' => false, 'message' => 'Device app can not created.'], 200);
            }
        } else {
            $device_app->language = $request->language;
            $client_token = $device_app->client_token;
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
        if (!$response['status']) {
            return response()->json(['result' => false, 'message' => 'Purchase not validated.'], 200);
        }
        $device_app->subscription = $response['status'];
        $device_app->receipt = $request->receipt;
        $is_updated = false;
        if ($device_app->expire_date != null) {
            $is_updated = true;
        }
        $device_app->expire_date = $response['expire-date'];
        if ($device_app->save()) {
        } else {
            return response()->json(['result' => false, 'message' => 'Error on save.'], 200);
        }
        if ($is_updated) {
            event(new Renewed($device_app));
        } else {
            event(new Started($device_app));
        }

        return response()->json(['result' => true, 'message' => $response['message'], 'status' => $response['status'], 'expire-date' => $response['expire-date']], 200);
    }

    public function checkSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_token' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['result' => false, 'message' => 'required client_token.'], 200);
        }
        $device_app = DeviceApp::where('client_token', $request->client_token)->first();
        if ($device_app == null) {
            return response()->json(['result' => false, 'message' => 'client_token does not match'], 200);
        }

        return response()->json(['result' => true, 'subscription' => $device_app->subscription], 200);
    }
}
