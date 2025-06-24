<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'max_invoices_yearly',
        'max_users',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function subscription(): HasMany
        {
            return $this->hasMany(Plan::class);
        }

}
