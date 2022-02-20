<?php

namespace App\Console\Commands;

use App\Jobs\CheckDeviceAppSubscription;
use App\Models\DeviceApp;
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
                CheckDeviceAppSubscription::dispatch($device_app);
            }
        });
    }
}
