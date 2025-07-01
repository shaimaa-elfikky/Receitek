<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\InvoiceResource\Pages;
use App\Models\Category;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Invoice Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('')
                ->schema([
                    Forms\Components\Select::make('client_id')
                        ->label('Client')
                        ->options(Client::where('tenant_id', Auth::id())->pluck('name_en', 'id'))
                        ->searchable()
                        ->required()
                        ->prefixIcon('heroicon-o-user'),
                    Forms\Components\TextInput::make('invoice_number')
                        ->default('INV-' . str_pad(Invoice::max('id') + 1, 4, '0', STR_PAD_LEFT))
                        ->required()
                        ->prefixIcon('heroicon-o-hashtag'),
                    Forms\Components\DatePicker::make('issue_date')
                        ->default(now())
                        ->required()
                        ->prefixIcon('heroicon-o-calendar'),
                    Forms\Components\DatePicker::make('due_date')
                        ->default(now()->addDays(14))
                        ->required()
                        ->prefixIcon('heroicon-o-clock'),
                    Forms\Components\Select::make('category_filter')
                        ->label('Category')
                        ->options(Category::where('tenant_id', Auth::id())->pluck('name', 'id')->toArray())
                        ->live()
                        ->afterStateUpdated(fn(Set $set) => $set('selected_items', []))
                        ->columnSpan(2)
                        ->prefixIcon('heroicon-o-tag'),
                    Forms\Components\Placeholder::make('items_placeholder')
                        ->content(function (Get $get) {
                            $categoryId = $get('category_filter');
                            if (!$categoryId) {
                                return 'Select a category to see available items';
                            }
                            $products = Product::where('tenant_id', Auth::id())
                                ->where('category_id', $categoryId)
                                ->count();
                            $services = Service::where('tenant_id', Auth::id())
                                ->where('category_id', $categoryId)
                                ->count();
                            $total = $products + $services;
                            if ($total === 0) {
                                return '❌ No items available in this category';
                            }
                        }),
                    Forms\Components\Grid::make(4)->schema(
                        function (Get $get) {
                            $categoryId = $get('category_filter');
                            if (!$categoryId) {
                                return [];
                            }
                            $products = Product::where('tenant_id', Auth::id())
                                ->where('category_id', $categoryId)
                                ->get();
                            $services = Service::where('tenant_id', Auth::id())
                                ->where('category_id', $categoryId)
                                ->get();
                            $items = [];
                            foreach ($products as $product) {
                                $items[] = Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make("add_product_{$product->id}")
                                        ->label($product->name)
                                        ->icon('heroicon-o-shopping-bag')
                                        ->color('primary')
                                        ->size('sm')
                                        ->extraAttributes(['class' => 'hover:scale-105 transition-transform'])
                                        ->action(function (Get $get, Set $set) use ($product) {
                                            $items = $get('items') ?? [];
                                            $found = false;
                                            foreach ($items as &$item) {
                                                if (($item['product_id'] ?? null) === $product->id) {
                                                    $item['quantity'] = ($item['quantity'] ?? 1) + 1;
                                                    $found = true;
                                                    break;
                                                }
                                            }
                                            unset($item);
                                            if (! $found) {
                                                $items[] = [
                                                    'description' => $product->name,
                                                    'quantity' => 1,
                                                    'unit_price' => $product->price,
                                                    'vat_rate' => $product->vat ?? 15,
                                                    'product_id' => $product->id,
                                                    'service_id' => null,
                                                    'discount_percentage' => 0,
                                                ];
                                            }
                                            $set('items', $items);
                                            self::updateTotals($get, $set);
                                        })
                                ]);
                            }
                            foreach ($services as $service) {
                                $items[] = Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make("add_service_{$service->id}")
                                        ->label($service->name)
                                        ->icon('heroicon-o-wrench-screwdriver')
                                        ->color('success')
                                        ->size('sm')
                                        ->extraAttributes(['class' => 'hover:scale-105 transition-transform'])
                                        ->action(function (Get $get, Set $set) use ($service) {
                                            $items = $get('items') ?? [];
                                            $found = false;
                                            foreach ($items as &$item) {
                                                if (($item['service_id'] ?? null) === $service->id) {
                                                    $item['quantity'] = ($item['quantity'] ?? 1) + 1;
                                                    $found = true;
                                                    break;
                                                }
                                            }
                                            unset($item);
                                            if (! $found) {
                                                $items[] = [
                                                    'description' => $service->name,
                                                    'quantity' => 1,
                                                    'unit_price' => $service->price,
                                                    'vat_rate' => $service->vat ?? 15,
                                                    'product_id' => null,
                                                    'service_id' => $service->id,
                                                    'discount_percentage' => 0,
                                                ];
                                            }
                                            $set('items', $items);
                                            self::updateTotals($get, $set);
                                        })
                                ]);
                            }
                            return $items;
                        }
                    ),
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->columnSpanFull()
                        ->schema([
                            Forms\Components\Grid::make(9)->schema([
                                Forms\Components\TextInput::make('description')
                                    ->label('Item Name')
                                    ->required()
                                    ->columnSpan(3)
                                    ->prefixIcon('heroicon-o-tag'),
                                Forms\Components\Hidden::make('product_id'),
                                Forms\Components\Hidden::make('service_id'),
                                Forms\Components\Hidden::make('vat_rate'),
                                Forms\Components\Select::make('product_serial_id')
                                    ->label('Product Serial')
                                    ->options(function (Get $get) {
                                        $productId = $get('product_id');
                                        if (!$productId) {
                                            return [];
                                        }
                                        return \App\Models\ProductSerial::where('product_id', $productId)
                                            ->pluck('serial_number', 'id');
                                    })
                                    ->visible(function (Get $get) {
                                        return !empty($get('product_id'));
                                    })
                                    ->required(function (Get $get) {
                                        $productId = $get('product_id');
                                        if (!$productId) {
                                            return false;
                                        }
                                        return \App\Models\ProductSerial::where('product_id', $productId)->exists();
                                    }),
                                Forms\Components\TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->live()
                                    ->columnSpan(1)
                                    ->prefixIcon('heroicon-o-hashtag'),
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->required()
                                    ->numeric()
                                    ->live()
                                    ->columnSpan(2)
                                    ->prefixIcon('heroicon-o-currency-dollar'),
                                Forms\Components\TextInput::make('discount_percentage')
                                    ->label('Discount (%)')
                                    ->numeric()
                                    ->default(0)
                                    ->live()
                                    ->columnSpan(1)
                                    ->prefixIcon('heroicon-o-tag'),
                                Forms\Components\Placeholder::make('total')
                                    ->label('Total')
                                    ->content(fn(Get $get) => '$' . number_format(($get('quantity') * $get('unit_price')) - (($get('quantity') * $get('unit_price')) * ($get('discount_percentage') / 100)), 2))
                                    ->columnSpan(1),
                            ]),
                        ])
                        ->live()
                        ->afterStateUpdated(fn(Get $get, Set $set) => self::updateTotals($get, $set))
                        ->deleteAction(fn(Get $get, Set $set) => self::updateTotals($get, $set))
                        ->itemLabel(fn (array $state): ?string => $state['description'] ?? null)
                        ->defaultItems(0)
                        ->minItems(0)
                        ->disableItemCreation(),
                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->placeholder('Add any additional notes here...')
                        ->rows(3)
                        ->columnSpan(3),
                    Forms\Components\Grid::make(1)->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Sub Total')
                            ->inlineLabel() 
                            ->numeric()
                            ->readOnly()
                            ->prefixIcon('heroicon-o-currency-dollar'),
                        Forms\Components\TextInput::make('total_discount')
                            ->label('Discount')
                            ->inlineLabel() 
                            ->numeric()
                            ->readOnly()
                            ->prefixIcon('heroicon-o-tag'),
                        Forms\Components\TextInput::make('taxable_amount')
                            ->label('Taxable Amount')
                            ->inlineLabel() 
                            ->numeric()
                            ->readOnly()
                            ->prefixIcon('heroicon-o-calculator'),
                        Forms\Components\TextInput::make('total_tax')
                            ->label('VAT')
                            ->inlineLabel() 
                            ->numeric()
                            ->readOnly()
                            ->prefixIcon('heroicon-o-calculator'),
                        Forms\Components\TextInput::make('total')
                            ->label('Total') 
                            ->inlineLabel()
                            ->numeric()
                            ->readOnly()
                            ->prefixIcon('heroicon-o-currency-dollar')
                            ->extraAttributes(['class' => 'font-bold text-lg']),
                    ]),
                  
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('invoice_number')->searchable(),
            Tables\Columns\TextColumn::make('client.name_en')->label('Client'),
            Tables\Columns\TextColumn::make('total')->money('SAR'),
            Tables\Columns\TextColumn::make('issue_date')->date(),
        ])->defaultSort('issue_date', 'desc');
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        $invoiceService = app(\App\Services\InvoiceService::class);
        $items = $get('items') ?? [];
        $totals = $invoiceService->calculateTotals($items);

        $set('subtotal', number_format($totals['subtotal'], 2, '.', ''));
        $set('total_discount', number_format($totals['total_discount'], 2, '.', ''));
        $set('total_tax', number_format($totals['total_tax'], 2, '.', ''));
        $set('total', number_format($totals['total'], 2, '.', ''));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tenant_id', Auth::id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
        ];
    }
}
