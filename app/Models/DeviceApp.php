<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class DeviceApp extends Model
{
    use UsesUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $dates = [
        'expire_date',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'app',
    ];

    //RELATIONS
    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id');
    }

    public function app()
    {
        return $this->belongsTo('App\Models\App', 'app_id');
    }

    protected $hidden = ['created_at', 'updated_at'];
    protected $table = 'device_app';
}
