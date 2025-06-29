<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'client_id',
        'invoice_id',
        'credit_note_number',
        'issue_date',
        'amount',
        'reason',
        'description',
        'notes',
        'terms',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
} 