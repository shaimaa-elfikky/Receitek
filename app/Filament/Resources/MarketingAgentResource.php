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

class MarketingAgentResource extends Resource
{
    protected static ?string $model = MarketingAgent::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Client Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Company Information')->schema([
                Forms\Components\TextInput::make('company_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_registration_number')
                    ->label('Registration Number')
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_vat_number')
                    ->label('VAT Number')
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_email')
                    ->label('Company Email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_phone')
                    ->label('Company Phone')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Textarea::make('company_address')
                    ->label('Company Address')
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Manager Information')->schema([
                Forms\Components\TextInput::make('manager_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('manager_email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('manager_phone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
            ])->columns(2),

            Forms\Components\Section::make('Lead Source')->schema([
                Forms\Components\Select::make('lead_source_id')
                    ->relationship('leadSource', 'name') // 'name' is a placeholder
                    // This closure intelligently finds the right display name
                    ->getOptionLabelFromRecordUsing(
                        fn(LeadSource $record) => $record->type === 'company'
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
                    // Use getStateUsing for Filament v2 to avoid issues with nulls
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
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // To show the tenants related to this agent, create a relation manager:
            // php artisan make:filament-relation-manager MarketingAgentResource tenants name
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