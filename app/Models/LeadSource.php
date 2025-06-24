<?php

namespace App\Models;

use App\Enums\LeadSourceType; // Make sure you created this Enum
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MarketingAgent;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadSource extends Model
{
        use HasFactory;

         
            protected $fillable = [
                'type',
                // Person fields
                'name',
                'email',
                'phone',
                // Company fields
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

    public function marketAgent(): HasMany
        {
            return $this->hasMany(MarketingAgent::class);
        }


}
