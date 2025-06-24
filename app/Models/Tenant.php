<?php

namespace App\Models;

use App\Models\MarketingAgent;
use App\Models\Subscription;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Tenant extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'marketing_agent_id',
        'company_name',
        'company_registration_number',
        'company_vat_number',
        'company_email',
        'company_phone',
        'company_address',
        'manager_name',
        'manager_email',
        'password',
        'manager_phone',
    ];

    protected $hidden = ['password'];

    protected $casts = ['password' => 'hashed'];

    public function canAccessPanel(Panel $panel): bool
    {
        // Only apply this logic to the 'app' panel
        if ($panel->getId() === 'app') {
            // The tenant must have at least one subscription that is:
            // 1. Has a status of 'active'.
            // 2. Has an end_date that is today or in the future.
            return $this->subscriptions()
                ->where('status', 'active')
                ->where('end_date', '>=', now()->toDateString())
                ->exists();
        }

        // By default, deny access to any other panels (like the admin panel)
        return false;
    }

   
    public function marketingAgent(): BelongsTo
    {
        return $this->belongsTo(MarketingAgent::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // Note: Your getFilamentName() method references 'entity_name', which is not
    // in your $fillable array. Filament will likely use 'company_name' by default
    // if it can't find 'entity_name'.
    public function getFilamentName(): string
    {
        return $this->company_name;
    }
}