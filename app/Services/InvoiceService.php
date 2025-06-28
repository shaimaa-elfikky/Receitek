<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
 

    /**
     * Calculate totals from invoice items
     */
    public function calculateTotals(array $items): array
    {
        $subTotal = 0;
        $totalDiscount = 0;
        $totalTax = 0;

        foreach ($items as $item) {
            $lineTotal = ($item['quantity'] * $item['unit_price']);
            $discountAmount = $lineTotal * (($item['discount_percentage'] ?? 0) / 100);
            $priceAfterDiscount = $lineTotal - $discountAmount;
            $taxPercentage = ($item['vat_rate'] === 'exempt' || $item['vat_rate'] === null) ? 0 : (float)$item['vat_rate'] / 100;
            $taxAmount = $priceAfterDiscount * $taxPercentage;
            
            $subTotal += $priceAfterDiscount;
            $totalDiscount += $discountAmount;
            $totalTax += $taxAmount;
        }

        return [
            'subtotal' => $subTotal,
            'total_discount' => $totalDiscount,
            'total_tax' => $totalTax,
            'total' => $subTotal + $totalTax,
        ];
    }

    /**
     * Format invoice data for form display
     */
    public function formatInvoiceForForm(Invoice $invoice): array
    {
        $data = $invoice->toArray();
        $items = [];
        
        foreach ($invoice->items as $item) {
            // Get the category ID properly
            $categoryId = null;
            if ($item->product_id && $item->product) {
                $categoryId = $item->product->category_id;
            } elseif ($item->service_id && $item->service) {
                $categoryId = $item->service->category_id;
            }
            
            $items[] = [
                'category_filter' => $categoryId,
                'item_id' => $item->product_id ? "product_{$item->product_id}" : "service_{$item->service_id}",
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_percentage' => $item->discount_percentage,
                'product_id' => $item->product_id,
                'service_id' => $item->service_id,
                'vat_rate' => $item->vat_rate,
            ];
        }
        
        $data['items'] = $items;
        return $data;
    }
} 