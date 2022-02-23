<?php

namespace App\Console\Commands;

use App\Jobs\GoogleMockReceiptVerify;
use App\Jobs\IOSMockReceiptVerify;
use App\Models\DeviceApp;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeviceAppSubscriptionCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:subscription_check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Device Subscription';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        DeviceApp::whereNotNull('expire_date')->chunk(10, function ($device_apps) {
            foreach ($device_apps as $device_app) {
                if ($device_app->expire_date == null) {
                    return;
                }
                $expire_date = $device_app->expire_date;
                if (Carbon::now()->gt($expire_date)) {
                    //Validate Receipt
                    if ($device_app->operating_system == 'android') {
                        GoogleMockReceiptVerify::dispatch($device_app);
                    } elseif ($device_app->operating_system == 'ios') {
                        IOSMockReceiptVerify::dispatch($device_app);
                    }
                }
            }
        });
    }
}
