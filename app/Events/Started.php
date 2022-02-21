<?php

namespace App\Events;

use App\Models\DeviceApp;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Started
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $device_app;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(DeviceApp $device_app)
    {
        $this->device_app = $device_app;
    }
}
