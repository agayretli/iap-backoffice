<?php

namespace App\Jobs;

use App\Events\Canceled;
use App\Models\DeviceApp;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckDeviceAppSubscription implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $device_app;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(DeviceApp $device_app)
    {
        $this->device_app = $device_app;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->device_app->expire_date == null) {
            return;
        }

        $expire_date = $this->device_app->expire_date;

        if (Carbon::now()->gt($expire_date)) {
            //todo mocking
            $this->device_app->expire_date = null;
            $this->device_app->subscription = 0;
            event(new Canceled($device_app));
        }
        $this->device_app->save();
    }
}
