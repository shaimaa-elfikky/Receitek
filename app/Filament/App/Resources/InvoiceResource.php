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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->schema([
                Forms\Components\Section::make('Invoice Details')->schema([
                    Forms\Components\Select::make('client_id')->label('Client')->options(Client::where('tenant_id', auth()->user()->id)->pluck('name_en', 'id'))->searchable()->required(),
                    Forms\Components\TextInput::make('invoice_number')->default('INV-' . str_pad(Invoice::max('id') + 1, 4, '0', STR_PAD_LEFT))->required(),
                    Forms\Components\DatePicker::make('issue_date')->default(now())->required(),
                    Forms\Components\DatePicker::make('due_date')->default(now()->addDays(14))->required(),
                ])->columns(2),

                Forms\Components\Section::make('Invoice Items')->schema([
                    Forms\Components\Repeater::make('items')
                    ->relationship()
                        ->schema([
                            Forms\Components\Grid::make(8)->schema([
                                Forms\Components\Select::make('category_filter')
                                    ->label('Category')
                                    ->options(Category::where('tenant_id', auth()->user()->id)->pluck('name', 'id')->toArray())
                                    ->live()
                                    ->afterStateUpdated(fn(Set $set) => $set('item_id', null))
                                    ->columnSpan(2),

                                Forms\Components\Select::make('item_id')
                                    ->label('Item')
                                    ->options(function (Get $get) {
                                        $categoryId = $get('category_filter');
                                        $productsQuery = Product::where('tenant_id', auth()->user()->id);
                                        $servicesQuery = Service::where('tenant_id', auth()->user()->id);

                                        if ($categoryId) {
                                            $productsQuery->where('category_id', $categoryId);
                                            $servicesQuery->where('category_id', $categoryId);
                                        }

                                        $products = $productsQuery->get()->mapWithKeys(fn($p) => ["product_{$p->id}" => $p->name])->toArray();
                                        $services = $servicesQuery->get()->mapWithKeys(fn($s) => ["service_{$s->id}" => $s->name])->toArray();

                                        // Merge the two arrays
                                        return array_merge($products, $services);
                                    })
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        // Defensive: clear all fields if state is not a string or not in the expected format
                                        if (!is_string($state) || !str_contains($state, '_')) {
                                            $set('description', null);
                                            $set('unit_price', null);
                                            $set('vat_rate', null);
                                            $set('product_id', null);
                                            $set('service_id', null);
                                            self::updateTotals($get, $set);
                                            return;
                                        }
                                        [$type, $id] = explode('_', $state, 2);
                                        if ($type === 'product' && is_numeric($id)) {
                                            $model = \App\Models\Product::find($id);
                                            if ($model) {
                                                $set('description', $model->name);
                                                $set('unit_price', $model->price);
                                                $set('vat_rate', $model->vat);
                                                $set('product_id', $id);
                                                $set('service_id', null);
                                            } else {
                                                // Clear if not found
                                                $set('description', null);
                                                $set('unit_price', null);
                                                $set('vat_rate', null);
                                                $set('product_id', null);
                                                $set('service_id', null);
                                            }
                                        } elseif ($type === 'service' && is_numeric($id)) {
                                            $model = \App\Models\Service::find($id);
                                            if ($model) {
                                                $set('description', $model->name);
                                                $set('unit_price', $model->price);
                                                $set('vat_rate', $model->vat);
                                                $set('service_id', $id);
                                                $set('product_id', null);
                                            } else {
                                                // Clear if not found
                                                $set('description', null);
                                                $set('unit_price', null);
                                                $set('vat_rate', null);
                                                $set('product_id', null);
                                                $set('service_id', null);
                                            }
                                        } else {
                                            // Clear if not a valid type/id
                                            $set('description', null);
                                            $set('unit_price', null);
                                            $set('vat_rate', null);
                                            $set('product_id', null);
                                            $set('service_id', null);
                                        }
                                        self::updateTotals($get, $set);
                                    })
                                    ->dehydrated(false)
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('description')->required()->columnSpan(4),
                                Forms\Components\Hidden::make('product_id'),
                                Forms\Components\Hidden::make('service_id'),
                                Forms\Components\Hidden::make('vat_rate'),
                                Forms\Components\TextInput::make('quantity')->required()->numeric()->default(1)->live()->columnSpan(2),
                                Forms\Components\TextInput::make('unit_price')->label('Unit Price')->required()->numeric()->live()->columnSpan(2),
                                Forms\Components\TextInput::make('discount_percentage')->label('Discount (%)')->numeric()->default(0)->live()->columnSpan(2),
                                Forms\Components\Placeholder::make('total')->label('Line Total')->content(fn(Get $get) => number_format(($get('quantity') * $get('unit_price')) - (($get('quantity') * $get('unit_price')) * ($get('discount_percentage') / 100)), 2))->columnSpan(2),
                            ]),
                        ])
                        ->live()
                        ->afterStateUpdated(fn(Get $get, Set $set) => self::updateTotals($get, $set))
                        ->deleteAction(fn(Get $get, Set $set) => self::updateTotals($get, $set)),
                ]),
            ])->columnSpan(['lg' => 2]),

            Forms\Components\Group::make()->schema([
                Forms\Components\Section::make('Status & Totals')->schema([
                    Forms\Components\Select::make('status')->options(['unpaid' => 'Unpaid', 'prepaid' => 'Pre-Paid', 'paid' => 'Paid'])->default('unpaid')->required(),
                    Forms\Components\TextInput::make('subtotal')->label('Sub Total')->numeric()->readOnly(),
                    Forms\Components\TextInput::make('total_discount')->label('Total Discount')->numeric()->readOnly(),
                    Forms\Components\TextInput::make('subtotal')->label('Taxable Amount')->numeric()->readOnly(),
                    Forms\Components\TextInput::make('total_tax')->label('Total VAT')->numeric()->readOnly(),
                    Forms\Components\TextInput::make('total')->numeric()->readOnly(),
                ]),
                Forms\Components\Section::make('Notes')->schema([Forms\Components\Textarea::make('notes'), Forms\Components\Textarea::make('terms')]),
            ])->columnSpan(['lg' => 1]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('invoice_number')->searchable(),
            Tables\Columns\TextColumn::make('client.name_en')->label('Client'),
            Tables\Columns\TextColumn::make('total')->money('SAR'),
            Tables\Columns\TextColumn::make('issue_date')->date(),
            Tables\Columns\TextColumn::make('status')->badge()->color(fn(string $state): string => match ($state) {
                'unpaid' => 'warning', 'prepaid' => 'info', 'paid' => 'success',default =>'unpaid',
            }),
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
        return parent::getEloquentQuery()->where('tenant_id', auth()->user()->id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
        
        ];
    }
}