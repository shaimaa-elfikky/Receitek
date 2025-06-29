<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model implements HasName
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'description',
        'sku',
        'code',
        'serial_number',
        'price',
        'is_active',
        'vat',
        'vat_included',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

  
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }


    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }


    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function serials()
    {
        return $this->hasMany(ProductSerial::class);
    }
}
