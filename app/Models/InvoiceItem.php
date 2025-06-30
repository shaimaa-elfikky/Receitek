<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_id','product_id','service_id','description', 'quantity', 'unit_price','discount','discount_percentage', 'vat_rate', 'vat_included', 'total'];

    protected static function booted()
    {
        static::creating(function ($item) {
            $item->total = ($item->quantity * $item->unit_price) - $item->discount;
        });
    }

    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
}
