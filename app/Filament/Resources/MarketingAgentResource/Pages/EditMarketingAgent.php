<?php

namespace App\Filament\Resources\MarketingAgentResource\Pages;

use App\Filament\Resources\MarketingAgentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarketingAgent extends EditRecord
{
    protected static string $resource = MarketingAgentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
