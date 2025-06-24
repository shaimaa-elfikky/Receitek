<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Models\Contracts\HasName;

class Client extends Model
{
    use HasFactory;

        protected $fillable = [
        'tenant_id',
        'client_type',
        'name_en',
        'name_ar',
        'email',
        'phone',
        'address',
        'cr_number',
        'vat_number',
        'building_no',
        'street_name',
        'district',
        'city',
        'country',
        'postal_code',
        'additional_no',
    ];

    public function getFilamentName(): string
    {
        return $this->name_en;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }


}
