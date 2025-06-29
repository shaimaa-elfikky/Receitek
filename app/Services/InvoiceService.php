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
            
            // Handle VAT included calculations
            $vatIncluded = $item['vat_included_value'] ?? false;
            
            if ($vatIncluded) {
                // If VAT is included, the unit price already contains VAT
                // We need to calculate the VAT amount from the price after discount
                $taxAmount = $priceAfterDiscount - ($priceAfterDiscount / (1 + $taxPercentage));
                $subTotal += $priceAfterDiscount - $taxAmount; // Add price without VAT to subtotal
            } else {
                // If VAT is not included, add VAT on top
                $taxAmount = $priceAfterDiscount * $taxPercentage;
                $subTotal += $priceAfterDiscount; // Add full price to subtotal
            }
            
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
                'vat_included_value' => $item->product_id ? $item->product->vat_included : ($item->service_id ? $item->service->vat_included : false),
            ];
        }
        
        $data['items'] = $items;
        return $data;
    }
} 