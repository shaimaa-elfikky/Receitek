<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ClientResource\Pages;
use App\Filament\App\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Client Management';


    // --- MULTI-TENANCY PART 1: Automatically set tenant_id on creation ---
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Client Type')->schema([
                Forms\Components\Select::make('client_type')
                    ->options([
                        'B2C' => 'Individual (B2C)',
                        'B2B' => 'Business (B2B)',
                    ])
                    ->required()
                    ->live(),
            ]),

            Forms\Components\Section::make('Client Details')->schema([
                Forms\Components\TextInput::make('name_en')
                    ->label('Name (English)')
                    ->required(),
                Forms\Components\TextInput::make('name_ar')
                    ->label('Name (Arabic)')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->prefixIcon('heroicon-o-envelope'),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->prefixIcon('heroicon-o-phone'),
            ])->columns(2),

            // --- B2C Specific Fields ---
            Forms\Components\Section::make('Address')
                ->visible(fn(Get $get): bool => $get('client_type') === 'B2C')
                ->schema([
                    Forms\Components\Textarea::make('address')
                        ->label('Client Address'),
                ]),

            // --- B2B Specific Fields ---
            Forms\Components\Section::make('Business & Address Details (B2B)')
                ->visible(fn(Get $get): bool => $get('client_type') === 'B2B')
                ->schema([
                    Forms\Components\TextInput::make('cr_number')
                        ->label('Commercial Registration (CR) Number'),
                    Forms\Components\TextInput::make('vat_number')
                        ->label('VAT Number'),
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('building_no'),
                        Forms\Components\TextInput::make('street_name'),
                        Forms\Components\TextInput::make('district'),
                        Forms\Components\TextInput::make('city'),
                        Forms\Components\TextInput::make('country'),
                        Forms\Components\TextInput::make('postal_code'),
                        Forms\Components\TextInput::make('additional_no'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_en')
                    ->label('Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client_type')
                    ->badge()
                    ->color(
                        fn(string $state): string => match ($state) {
                            'B2C' => 'success',
                            'B2B' => 'info',
                        }
                    ),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('phone')->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('client_type')->options([
                    'B2C' => 'Individual (B2C)',
                    'B2B' => 'Business (B2B)',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // --- MULTI-TENANCY PART 2: Automatically scope queries to the logged-in tenant ---
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(
            'tenant_id',
            Auth::user()->id
        );
    }

    // --- MULTI-TENANCY PART 3: Automatically set tenant_id on creation ---
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = Auth::user()->id;
        return $data;
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
