<?php

namespace App\Filament\App\Resources\CreditNoteResource\Pages;

use App\Filament\App\Resources\CreditNoteResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCreditNote extends CreateRecord
{
    protected static string $resource = CreditNoteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = Auth::user()->id;
        
        return $data;
    }
} 