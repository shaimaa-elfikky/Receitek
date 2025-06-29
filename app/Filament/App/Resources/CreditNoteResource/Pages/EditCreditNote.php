<?php

namespace App\Filament\App\Resources\CreditNoteResource\Pages;

use App\Filament\App\Resources\CreditNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCreditNote extends EditRecord
{
    protected static string $resource = CreditNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 