<?php

namespace App\Filament\Resources\MarketingAgentResource\Pages;

use App\Filament\Resources\MarketingAgentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketingAgents extends ListRecords
{
    protected static string $resource = MarketingAgentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
