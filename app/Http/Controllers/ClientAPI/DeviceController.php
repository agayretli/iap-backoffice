<?php

namespace App\Http\Controllers\ClientAPI;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\Device;
use App\Models\DeviceApp;
use Carbon\Carbon;
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
            'receipt' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['result' => false, 'message' => 'required parameters.'], 200);
        }

        //todo mocking
        //parse receipt

        $device_app = DeviceApp::where('client_token', $request->client_token)->first();
        if ($device_app == null) {
            return response()->json(['result' => false, 'message' => 'client_token does not match'], 200);
        }
        $device_app->subscription = 1;
        $date = Carbon::now()->addMonth();
        $device_app->expire_date = $date;
        if ($device_app->save()) {
        } else {
            return response()->json(['result' => false, 'message' => 'Error on save.'], 200);
        }
        $expire_date = $date->setTimezone('America/Belize')->format('Y-m-d H:i:s');

        return response()->json(['result' => true, 'message' => 'OK', 'status' => ($device_app->subscription) ? true : false, 'expire-date' => $expire_date], 200);
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
