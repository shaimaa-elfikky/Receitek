<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebitNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'client_id',
        'invoice_id',
        'debit_note_number',
        'issue_date',
        'due_date',
        'amount',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
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

    /**
     * Check if the debit note is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date->isPast();
    }

    /**
     * Check if the debit note can be paid
     */
    public function canBePaid(): bool
    {
        return in_array($this->status, ['draft', 'sent']);
    }

    /**
     * Check if the debit note can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'sent']);
    }

    /**
     * Get the status color for display
     */
    public function getStatusColorAttribute(): string
    {
        switch($this->status) {
            case 'draft':
                return 'gray';
            case 'sent':
                return 'blue';
            case 'paid':
                return 'green';
            case 'cancelled':
                return 'red';
            default:
                return 'gray';
        }
    }

    /**
     * Scope to get outstanding debit notes
     */
    public function scopeOutstanding($query)
    {
        return $query->whereIn('status', ['draft', 'sent']);
    }

    /**
     * Scope to get paid debit notes
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
} 