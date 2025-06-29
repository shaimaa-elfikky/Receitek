<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSerial extends Model
{
    protected $fillable=[
        'product_id',
        'serial_number',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }


}
