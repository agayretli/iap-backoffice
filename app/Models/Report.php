<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
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
        'created_at',
        'updated_at',
    ];

    protected $hidden = ['created_at', 'updated_at'];
    protected $table = 'reports';
}
