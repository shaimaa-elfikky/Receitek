<?php

namespace App\Models;

use App\Models\MarketingAgent;
use App\Models\Subscription;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Tenant extends Authenticatable implements FilamentUser , HasName
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
        if ($panel->getId() === 'app') {
            return $this->subscriptions()
                ->where('status', 1)
                ->where('end_date', '>=', now()->toDateString())
                ->exists();
        }
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

    /**
     * This is the corrected method.
     * It ensures a name is always returned, preventing the error.
     */
    public function getFilamentName(): string
    {
        return $this->company_name ?? $this->manager_name ?? $this->manager_email;
    }
}