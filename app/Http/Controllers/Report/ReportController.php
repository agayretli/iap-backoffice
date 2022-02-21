<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\DeviceApp;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {
    }

    public function addRecords(DeviceApp $device_app, $subscription_status)
    {
        $report = new Report();
        $report->device_id = $device_app->device_id;
        $report->app_id = $device_app->app_id;
        $report->subscription_status = $subscription_status;
        $report->operating_system = $device_app->operating_system;
        $report->save();
    }

    public function records(Request $request)
    {
        if (empty($request->search)) {
            $posts = Report::get();
            $totalData = $posts->count();
        } else {
            $search = $request->search;
            $posts = Report::where('device_id', $search)
                               ->orWhere('app_id', $search)
                               ->orWhere('subscription_status', $search)
                               ->orWhere('operating_system', $search)
                               ->orWhere('created_at', 'LIKE', $search.'%')
                               ->get();

            $totalData = $posts->count();
        }

        $json_data = [
                   'records' => intval($totalData),
                   'data' => $posts,
                   ];

        return response()
           ->json($json_data);
    }
}
