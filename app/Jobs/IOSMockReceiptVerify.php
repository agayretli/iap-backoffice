<?php

namespace App\Jobs;

use App\Events\Canceled;
use App\Events\Renewed;
use App\Models\DeviceApp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IOSMockReceiptVerify implements ShouldQueue
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
        $response = app('App\Http\Controllers\Validation\ValidateController')->iosVerify($this->device_app->receipt);
        if ($response->getStatusCode() == 200 || $response->getStatusCode() == 201) {
            $response_body = $response->getBody();
            if (!(is_null($response_body) || empty($response_body))) {
                $body = json_decode($response_body, true);

                if ($body['status']) {
                    $this->device_app->expire_date = $body['expire_date'];
                    event(new Renewed($this->device_app));
                } else {
                    $this->device_app->expire_date = null;
                    $this->device_app->subscription = false;
                    $this->device_app->receipt = null;
                    event(new Canceled($this->device_app));
                }
                $this->device_app->save();
            }
        } else {
            $this->dispatch($this->device_app)
                ->onConnection($this->connection)
                ->onQueue($this->queue)
                ->delay(30);
        }
    }
}
