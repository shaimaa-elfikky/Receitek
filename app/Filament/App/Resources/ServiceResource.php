<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ServiceResource\Pages;
use App\Filament\App\Resources\ServiceResource\RelationManagers;
use App\Models\Category;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Products & Services';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Card::make([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->label('Code')
                    ->maxLength(100),
                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->options(
                        Category::where('tenant_id', Auth::id())
                            ->pluck('name', 'id')
                    )
                    ->searchable(),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('SAR'),
                Forms\Components\Select::make('vat')
                    ->label('VAT Rate')
                    ->options([
                        '15' => '15%',
                        '0' => '0%',
                        'exempt' => 'Exempt from VAT',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('vat_included')
                    ->label('Price includes VAT'),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->required()
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('SAR') 
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')->label(
                    'Active'
                ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->relationship(
                    'category',
                    'name'
                ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Secures the LIST VIEW for multi-tenancy.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where(
            'tenant_id',
            Auth::id()
        );
    }

    /**
     * Secures the CREATE action for multi-tenancy (Filament v2).
     */
    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = Auth::id();
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
