<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarketingAgentResource\Pages;
use App\Filament\Resources\MarketingAgentResource\RelationManagers;
use App\Models\LeadSource;
use App\Models\MarketingAgent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// Import the Wizard component for a better UI
use Filament\Forms\Components\Wizard;

class MarketingAgentResource extends Resource
{
    protected static ?string $model = MarketingAgent::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Client Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Replaced the separate sections with a clean, step-by-step Wizard
            Wizard::make([
                Wizard\Step::make('Company Information')
                    ->description("Enter the details of the agent's company.")
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->required()
                            ->placeholder('e.g., Global Marketing Solutions')
                            ->prefixIcon('heroicon-o-building-office-2')
                            ->maxLength(255),
                        Forms\Components\TextInput::make(
                            'company_registration_number'
                        )
                            ->label('Registration Number')
                            ->placeholder('e.g., 123456-7890')
                            ->prefixIcon('heroicon-o-identification')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_vat_number')
                            ->label('VAT Number')
                            ->placeholder('e.g., VAT123456789')
                            ->prefixIcon('heroicon-o-receipt-percent')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_email')
                            ->label('Company Email')
                            ->email()
                            ->placeholder('e.g., contact@globalmarketing.com')
                            ->prefixIcon('heroicon-o-envelope')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_phone')
                            ->label('Company Phone')
                            ->tel()
                            ->placeholder('e.g., +1 555-111-2222')
                            ->prefixIcon('heroicon-o-phone')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('company_address')
                            ->label('Company Address')
                            ->placeholder('123 Business Rd, Commerce City')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Wizard\Step::make('Manager Information')
                    ->description(
                        'Provide the contact details for the primary manager.'
                    )
                    ->schema([
                        Forms\Components\TextInput::make('manager_name')
                            ->required()
                            ->placeholder('e.g., Sarah Connor')
                            ->prefixIcon('heroicon-o-user-circle')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('manager_email')
                            ->email()
                            ->required()
                            ->placeholder('e.g., s.connor@globalmarketing.com')
                            ->prefixIcon('heroicon-o-envelope')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('manager_phone')
                            ->tel()
                            ->required()
                            ->placeholder('e.g., +1 555-333-4444')
                            ->prefixIcon('heroicon-o-phone')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Wizard\Step::make('Lead Source')
                    ->description(
                        'Optionally, link this agent to the lead source that brought them in.'
                    )
                    ->schema([
                        Forms\Components\Select::make('lead_source_id')
                            ->relationship('leadSource', 'name')
                            ->getOptionLabelFromRecordUsing(
                                fn(
                                    LeadSource $record
                                ) => $record->type === 'company'
                                    ? $record->company_name
                                    : $record->name
                            )
                            ->searchable([
                                'name',
                                'company_name',
                                'email',
                                'company_email',
                            ])
                            ->preload()
                            ->nullable(),
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('manager_name')->searchable(),
                Tables\Columns\TextColumn::make('manager_phone')->searchable(),
                Tables\Columns\TextColumn::make('leadSource.name')
                    ->label('Lead Source')
                    ->getStateUsing(function (MarketingAgent $record) {
                        if (!$record->leadSource) {
                            return 'N/A';
                        }
                        return $record->leadSource->type === 'company'
                            ? $record->leadSource->company_name
                            : $record->leadSource->name;
                    })
                    ->searchable([
                        'leadSource.name',
                        'leadSource.company_name',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-magnifying-glass')
                    ->iconButton()
                    ->color('primary')
                    ->tooltip('View'),
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->iconButton()
                    ->color('warning')
                    ->tooltip('Edit'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
                //
            ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketingAgents::route('/'),
            'create' => Pages\CreateMarketingAgent::route('/create'),
            'view' => Pages\ViewMarketingAgent::route('/{record}'),
            'edit' => Pages\EditMarketingAgent::route('/{record}/edit'),
        ];
    }
}
