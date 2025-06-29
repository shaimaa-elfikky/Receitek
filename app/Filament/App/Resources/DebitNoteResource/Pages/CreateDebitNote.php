<?php

namespace App\Filament\App\Resources\DebitNoteResource\Pages;

use App\Filament\App\Resources\DebitNoteResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDebitNote extends CreateRecord
{
    protected static string $resource = DebitNoteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = Auth::user()->id;
        
        return $data;
    }
} 