<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\DeviceApp;
use App\Models\Report;
use Exception;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {
    }

    public function addRecord(DeviceApp $device_app, $subscription_status)
    {
        try {
            $report = new Report();
            $report->device_id = $device_app->device_id;
            $report->app_id = $device_app->app_id;
            $report->subscription_status = $subscription_status;
            $report->operating_system = $device_app->operating_system;
            $report->save();
        } catch (Exception $e) {
        }
    }

    public function records(Request $request)
    {
        return Report::paginate(10);
    }
}
