<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;


class Tax extends Model
{


    protected $casts = [
        'included' => 'boolean',
    ];

    public $timestamps = false;
}
