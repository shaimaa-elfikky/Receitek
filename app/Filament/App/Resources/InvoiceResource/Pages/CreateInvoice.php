<?php

namespace App\Filament\App\Resources\InvoiceResource\Pages;

use App\Filament\App\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

      protected function mutateFormDataBeforeCreate(array $data): array
        {
            $data['tenant_id'] = auth()->user()->id;
            return $data;
        }
}
