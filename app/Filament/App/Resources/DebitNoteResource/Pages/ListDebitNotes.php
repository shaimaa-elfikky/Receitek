<?php

namespace App\Filament\App\Resources\DebitNoteResource\Pages;

use App\Filament\App\Resources\DebitNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDebitNotes extends ListRecords
{
    protected static string $resource = DebitNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 