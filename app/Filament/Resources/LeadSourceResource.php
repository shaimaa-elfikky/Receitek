<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadSourceResource\Pages;
use App\Filament\Resources\LeadSourceResource\RelationManagers;
use App\Models\LeadSource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeadSourceResource extends Resource
{
    protected static ?string $model = LeadSource::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';
    protected static ?string $navigationGroup = 'Marketing Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')
                ->options([
                    'person' => 'Person',
                    'company' => 'Company',
                ])
                ->required()
                ->live() // This makes the form reactive to changes
                ->afterStateUpdated(function (Forms\Set $set) {
                    // This is good practice to clear out fields when switching types
                    $set('name', null);
                    $set('email', null);
                    $set('phone', null);
                    $set('company_name', null);
                    $set('company_registration_number', null);
                    $set('company_vat_number', null);
                    $set('company_email', null);
                    $set('company_phone', null);
                    $set('company_address', null);
                    $set('manager_name', null);
                    $set('manager_email', null);
                    $set('manager_phone', null);
                }),

            // --- Fields for Person ---
            Forms\Components\Section::make('Person Details')
                // The `visible()` method shows this section only if the condition is met
                ->visible(fn(Get $get): bool => $get('type') === 'person')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required(fn(Get $get): bool => $get('type') === 'person')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('phone')
                        ->tel()
                        ->maxLength(255),
                ])
                ->columns(2),

            // --- Fields for Company ---
            Forms\Components\Group::make()
                ->visible(fn(Get $get): bool => $get('type') === 'company')
                ->schema([
                    Forms\Components\Section::make('Company Details')
                        ->schema([
                            Forms\Components\TextInput::make('company_name')
                                ->required(
                                    fn(Get $get): bool => $get('type') ===
                                        'company'
                                )
                                ->maxLength(255),
                            Forms\Components\TextInput::make(
                                'company_registration_number'
                            )->maxLength(255),
                            Forms\Components\TextInput::make(
                                'company_vat_number'
                            )->maxLength(255),
                            Forms\Components\TextInput::make('company_email')
                                ->email()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('company_phone')
                                ->tel()
                                ->maxLength(255),
                            Forms\Components\Textarea::make('company_address')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    Forms\Components\Section::make('Manager Details')
                        ->schema([
                            Forms\Components\TextInput::make('manager_name')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('manager_email')
                                ->email()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('manager_phone')
                                ->tel()
                                ->maxLength(255),
                        ])
                        ->columns(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('type')
                ->badge()
                ->color(
                    fn(string $state): string => match ($state) {
                        'person' => 'success',
                        'company' => 'info',
                    }
                ),

            // --- CORRECTED NAME COLUMN for v2 ---
            Tables\Columns\TextColumn::make('name')
                ->label('Name / Company')
                // Use getStateUsing for Filament v2
                ->getStateUsing(function (LeadSource $record) {
                    return $record->type === 'company'
                        ? $record->company_name
                        : $record->name;
                })
                ->searchable(['name', 'company_name'])
                ->sortable(['name', 'company_name']),

            // --- CORRECTED EMAIL COLUMN for v2 ---
            Tables\Columns\TextColumn::make('email')
                ->label('Contact Email')
                // Use getStateUsing for Filament v2
                ->getStateUsing(function (LeadSource $record) {
                    return $record->type === 'company'
                        ? $record->manager_email
                        : $record->email;
                })
                ->searchable(['email', 'manager_email']),

            // --- CORRECTED PHONE COLUMN for v2 ---
            Tables\Columns\TextColumn::make('phone')
                ->label('Contact Phone')
                // Use getStateUsing for Filament v2
                ->getStateUsing(function (LeadSource $record) {
                    return $record->type === 'company'
                        ? $record->manager_phone
                        : $record->phone;
                })
                ->searchable(['phone', 'manager_phone']),

            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('type')->options([
                'person' => 'Person',
                'company' => 'Company',
            ]),
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
                // You can create a relation manager for the 'marketAgent' relationship
                // to show linked agents on the View page.
                // Run: php artisan make:filament-relation-manager LeadSourceResource marketAgent company_name
            ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeadSources::route('/'),
            'create' => Pages\CreateLeadSource::route('/create'),
            'view' => Pages\ViewLeadSource::route('/{record}'),
            'edit' => Pages\EditLeadSource::route('/{record}/edit'),
        ];
    }
}
