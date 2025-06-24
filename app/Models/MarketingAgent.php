<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LeadSource;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingAgent extends Model
{

    use HasFactory;

     protected $fillable = [
        'lead_source_id',
        'company_name',
        'company_registration_number',
        'company_vat_number',
        'company_email',
        'company_phone',
        'company_address',
        'manager_name',
        'manager_email',
        'manager_phone',
    ];

       public function leadSource(): BelongsTo
        {
            return $this->belongsTo(LeadSource::class);
        }

          public function tenants(): HasMany
        {
            return $this->hasMany(Tenant::class);
        }



}
