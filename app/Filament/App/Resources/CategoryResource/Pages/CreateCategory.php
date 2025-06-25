<?php

namespace App\Filament\App\Resources\CategoryResource\Pages;

use App\Filament\App\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

     protected function mutateFormDataBeforeCreate(array $data): array
        {
            $data['tenant_id'] = auth()->user()->id;
            return $data;
        }
}
