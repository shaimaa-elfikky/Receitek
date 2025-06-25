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
        'price',
        'quantity',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Defines the relationship to the Tenant that owns this product.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Defines the relationship to the Category this product belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Tells Filament how to get a display name for this model.
     */
    public function getFilamentName(): string
    {
        return $this->name;
    }
}
