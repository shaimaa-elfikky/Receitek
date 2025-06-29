<?php

namespace App\Filament\App\Resources\ProductResource\Pages;

use App\Filament\App\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Models\ProductSerial;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
        {
            $data['tenant_id'] = auth()->user()->id;
            return $data;
        }

    protected function afterCreate(): void
        {

          $serialNumbersInput = $this->form->getRawState()['serial_numbers'] ?? '';

            $serialNumbers = explode(',', $serialNumbersInput);

            foreach ($serialNumbers as $serial) {
                $serial = trim($serial);

                if (!$serial) continue;

                if (ProductSerial::where('serial_number', $serial)->exists()) {
                    $skippedSerials[] = $serial;
                    continue;
                }

                ProductSerial::create([
                    'product_id' => $this->record->id,
                    'serial_number' => $serial,
                ]);
                }

                if (!empty($skippedSerials)) {
                    Notification::make()
                        ->title('Some serial numbers were skipped (already exist).')
                        ->body(implode(', ', $skippedSerials))
                        ->warning()
                        ->send();
                }

        }
}
