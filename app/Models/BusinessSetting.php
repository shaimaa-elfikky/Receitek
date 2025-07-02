<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{

    protected $casts = [
        'value' => 'json'
    ];

    public $timestamps = false;
}
