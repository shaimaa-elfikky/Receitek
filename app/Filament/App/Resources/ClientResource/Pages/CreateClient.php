<?php

namespace App\Filament\App\Resources\ClientResource\Pages;

use App\Filament\App\Resources\ClientResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
        {
            $data['tenant_id'] = auth()->user()->id;
            return $data;
        }
}
