<?php

namespace App\Models;


use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model implements HasName
{
     use HasFactory;

    protected $fillable = ['tenant_id', 'name', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Defines the relationship to the Tenant that owns this category.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Tells Filament how to get a display name for this model.
     */
    public function getFilamentName(): string
    {
        return $this->name;
    }
}
