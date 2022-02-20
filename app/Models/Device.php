<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function device_apps()
    {
        return $this->hasMany('App\Models\DeviceApp', 'device_id');
    }

    public function apps()
    {
        return $this->hasManyThrough('App\Models\App', 'App\Models\DeviceApp', 'device_id', 'id', 'id', 'app_id');
    }
    protected $hidden = ['created_at', 'updated_at'];
    protected $appends = ['apps'];
    protected $table = 'devices';
}
