<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'client_id', 'invoice_number', 'issue_date', 'due_date',
        'subtotal', 'total_discount', 'total_tax','tax_rate', 'tax_amount', 'tax_amount','total', 'notes', 'terms',
    ];

    protected $casts = [] ;


    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function items(): HasMany { return $this->hasMany(InvoiceItem::class); }
    public function debitNotes(): HasMany { return $this->hasMany(DebitNote::class); }
    public function creditNotes(): HasMany { return $this->hasMany(CreditNote::class); }
}
