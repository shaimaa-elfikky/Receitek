<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Filament\Resources\TenantResource\RelationManagers;
use App\Models\MarketingAgent;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
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

            Forms\Components\Section::make('Manager & Login Details')->schema([
                Forms\Components\TextInput::make('manager_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('manager_email')
                    ->label('Manager Email (Login)')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('manager_phone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    // Required only when creating a new tenant
                    ->required(fn(string $context): bool => $context === 'create')
                    // Only save the password if a new one is entered
                    ->dehydrated(fn($state) => filled($state))
                    // The 'hashed' cast on the model handles the hashing
                    ->maxLength(255),
            ])->columns(2),

            Forms\Components\Section::make('Source')->schema([
                Forms\Components\Select::make('marketing_agent_id')
                    ->relationship('marketingAgent', 'company_name')
                    ->searchable()
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
                Tables\Columns\TextColumn::make('manager_email')->searchable(),
                Tables\Columns\TextColumn::make('marketingAgent.company_name')
                    ->label('Marketing Agent')
                    ->searchable(),
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
            // To show this tenant's subscriptions, create a relation manager:
            // php artisan make:filament-relation-manager TenantResource subscriptions plan_id
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'view' => Pages\ViewTenant::route('/{record}'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}