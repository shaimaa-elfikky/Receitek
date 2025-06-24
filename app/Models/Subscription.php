<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'start_date',
        'end_date',
        'extra_invoices',
        'extra_users',
        'status',
    ];
    
    protected $casts = [
        'status' => 'boolean',
    ];

    public function tenant(): BelongsTo
        {
            return $this->belongsTo(Tenant::class);
        }

    public function plan(): BelongsTo
        {
            return $this->belongsTo(Plan::class);
        }

}
