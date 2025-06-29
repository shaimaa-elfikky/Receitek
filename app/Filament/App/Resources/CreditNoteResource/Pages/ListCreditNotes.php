<?php

namespace App\Filament\App\Resources\CreditNoteResource\Pages;

use App\Filament\App\Resources\CreditNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCreditNotes extends ListRecords
{
    protected static string $resource = CreditNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 