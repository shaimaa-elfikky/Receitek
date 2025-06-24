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

// ENHANCEMENT: Import Wizard and Step components for a better UI
use Filament\Forms\Components\Wizard;

class LeadSourceResource extends Resource
{
    protected static ?string $model = LeadSource::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';
    protected static ?string $navigationGroup = 'Marketing Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Lead Source Type')
                ->description('Select whether this lead is an individual person or a company.')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->options([
                            'person' => 'Person',
                            'company' => 'Company',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn(Forms\Set $set) => $set('name', null)),
                ]),

            // --- Fields for Person ---
            Forms\Components\Section::make('Person Details')
                ->visible(fn(Get $get): bool => $get('type') === 'person')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required(fn(Get $get): bool => $get('type') === 'person')
                        ->placeholder('e.g., John Doe')
                        ->prefixIcon('heroicon-o-user')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->placeholder('e.g., john.doe@example.com')
                        ->prefixIcon('heroicon-o-envelope')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('phone')
                        ->tel()
                        ->placeholder('e.g., +1 555-123-4567')
                        ->prefixIcon('heroicon-o-phone')
                        ->maxLength(255),
                ])
                ->columns(2),

            // --- NEW: Wizard for Company ---
            // This provides a much cleaner step-by-step process for the user.
            Wizard::make([
                Wizard\Step::make('Company Details')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->required(fn(Get $get): bool => $get('type') === 'company')
                            ->placeholder('e.g., Acme Corporation')
                            ->prefixIcon('heroicon-o-building-office-2')
                            ->columnSpanFull()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_registration_number')
                            ->placeholder('e.g., 123456-7890')
                            ->prefixIcon('heroicon-o-identification')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_vat_number')
                            ->placeholder('e.g., VAT123456789')
                            ->prefixIcon('heroicon-o-receipt-percent')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_email')
                            ->email()
                            ->placeholder('e.g., contact@acme.corp')
                            ->prefixIcon('heroicon-o-envelope')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_phone')
                            ->tel()
                            ->placeholder('e.g., +1 555-987-6543')
                            ->prefixIcon('heroicon-o-phone')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('company_address')
                            ->placeholder('123 Main Street, Anytown, USA')
                            ->columnSpanFull(),
                    ]),
                ]),
                Wizard\Step::make('Manager Details')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('manager_name')
                            ->placeholder('e.g., Jane Smith')
                            ->prefixIcon('heroicon-o-user-circle')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('manager_email')
                            ->email()
                            ->placeholder('e.g., jane.smith@acme.corp')
                            ->prefixIcon('heroicon-o-envelope')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('manager_phone')
                            ->tel()
                            ->placeholder('e.g., +1 555-555-5555')
                            ->prefixIcon('heroicon-o-phone')
                            ->maxLength(255),
                    ]),
                ]),
            ])->visible(fn(Get $get): bool => $get('type') === 'company'),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Name / Company')
                    ->getStateUsing(function (LeadSource $record) {
                        return $record->type === 'company'
                            ? $record->company_name
                            : $record->name;
                    })
                    ->searchable(['name', 'company_name'])
                    ->sortable(['name', 'company_name']),
                Tables\Columns\TextColumn::make('email')
                    ->label('Contact Email')
                    ->getStateUsing(function (LeadSource $record) {
                        return $record->type === 'company'
                            ? $record->manager_email
                            : $record->email;
                    })
                    ->searchable(['email', 'manager_email']),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Contact Phone')
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
            ])
            // ENHANCEMENT: Show newest leads first by default.
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
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
