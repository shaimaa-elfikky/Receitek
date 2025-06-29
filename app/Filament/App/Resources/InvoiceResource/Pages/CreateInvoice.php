<?php

namespace App\Filament\App\Resources\InvoiceResource\Pages;

use App\Filament\App\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = Auth::user()->id;
        return $data;
    }
    protected function created($record): void
    {
        $items = $this->data['items'] ?? [];

        foreach ($items as $item) {
            $record->items()->create([
                'category_id' => $item['category_filter'] ?? null,
                'product_id' => $item['product_id'] ?? null,
                'service_id' => $item['service_id'] ?? null,
                'description' => $item['description'] ?? '',
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => $item['unit_price'] ?? 0,
                'vat_rate' => $item['vat_rate'] ?? 0,
                'vat_included' => $item['vat_included_value'] ?? false,
                'discount_percentage' => $item['discount_percentage'] ?? 0,
                'total' => $item['total'] ?? 0,
            ]);
        }
    }


}
