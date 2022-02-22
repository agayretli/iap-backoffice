<?php

namespace App\Jobs;

use App\Events\Canceled;
use App\Events\Renewed;
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
            //Validate Receipt
            if ($this->device_app->operating_system == 'android') {
                $response = app('App\Http\Controllers\Validation\ValidateController')->googleVerify($this->device_app->receipt);
            } else {
                $response = app('App\Http\Controllers\Validation\ValidateController')->iosVerify($this->device_app->receipt);
            }
            if ($response['status']) {
                $this->device_app->expire_date = $response['expire-date'];
                event(new Renewed($this->device_app));
            } else {
                $this->device_app->expire_date = null;
                $this->device_app->subscription = false;
                $this->device_app->receipt = null;
                event(new Canceled($this->device_app));
            }
        }
        $this->device_app->save();
    }
}
