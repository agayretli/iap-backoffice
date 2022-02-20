<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class App extends Model
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

    //RELATIONS
    public function device_apps()
    {
        return $this->hasMany('App\Models\DeviceApp', 'app_id');
    }

    public function devices()
    {
        return $this->hasManyThrough('App\Models\Device', 'App\Models\DeviceApp', 'app_id', 'id', 'id', 'device_id');
    }

    protected $hidden = ['created_at', 'updated_at'];
    protected $table = 'apps';
}
