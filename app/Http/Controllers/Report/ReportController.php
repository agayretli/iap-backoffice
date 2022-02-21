<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {
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
