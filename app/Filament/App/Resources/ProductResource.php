<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ProductResource\Pages;
use App\Filament\App\Resources\ProductResource\RelationManagers;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Products & Services';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Product Details')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]),
            Forms\Components\Section::make('Pricing & Stock')->schema([
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('SAR'), // Or your currency
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('sku')
                    ->label('SKU (Stock Keeping Unit)')
                    ->maxLength(255),
            ])->columns(2),
            Forms\Components\Section::make('Organization')->schema([
                // THIS IS A CRITICAL SECURITY & USABILITY FEATURE
                // It ensures that the dropdown only shows categories
                // that belong to the currently logged-in tenant.
                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->options(
                        Category::where('tenant_id', auth()->user()->id)
                            ->pluck('name', 'id')
                    )
                    ->searchable(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->required()
                    ->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('price')
                    ->money('SAR') // Or your currency
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity'),
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
            auth()->user()->id
        );
    }

    /**
     * Secures the CREATE action for multi-tenancy (Filament v2).
     */
    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->id;
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
