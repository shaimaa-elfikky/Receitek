<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\DebitNoteResource\Pages;
use App\Models\DebitNote;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Services\InvoiceService;

class DebitNoteResource extends Resource
{
    protected static ?string $model = DebitNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('invoice_id')
                    ->relationship('invoice', 'invoice_number')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $invoice = Invoice::with('items')->find($state);
                        if ($invoice) {
                            $items = [];
                            foreach ($invoice->items as $item) {
                                $items[] = [
                                    'description' => $item->description,
                                    'quantity' => $item->quantity,
                                    'unit_price' => $item->unit_price,
                                    'vat_rate' => $item->vat_rate,
                                    'product_id' => $item->product_id,
                                    'service_id' => $item->service_id,
                                    'discount_percentage' => $item->discount_percentage,
                                ];
                            }
                            $set('items', $items);
                        } else {
                            $set('items', []);
                        }
                    }),

                Forms\Components\Repeater::make('items')
                    ->schema([
                        Forms\Components\Grid::make(8)->schema([
                            Forms\Components\TextInput::make('description')
                                ->required()
                                ->columnSpan(4)
                                ->disabled(),
                            Forms\Components\Hidden::make('product_id'),
                            Forms\Components\Hidden::make('service_id'),
                            Forms\Components\Hidden::make('vat_rate'),
                            Forms\Components\TextInput::make('quantity')
                                ->required()
                                ->numeric()
                                ->live()
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('unit_price')
                                ->label('Unit Price')
                                ->required()
                                ->numeric()
                                ->live()
                                ->columnSpan(1)
                                ->disabled(),
                            Forms\Components\TextInput::make('discount_percentage')
                                ->label('Discount (%)')
                                ->numeric()
                                ->default(0)
                                ->live()
                                ->columnSpan(1),
                            Forms\Components\Placeholder::make('total')
                                ->label('Line Total')
                                ->content(fn(Get $get) => '$' . number_format(($get('quantity') * $get('unit_price')) - (($get('quantity') * $get('unit_price')) * ($get('discount_percentage') / 100)), 2))
                                ->columnSpan(1),
                        ]),
                    ])
                    ->disableItemCreation()
                    ->disableItemDeletion()
                    ->minItems(0)
                    ->defaultItems(0)
                    ->columnSpan(2)
                    ->live()
                    ->afterStateUpdated(fn(Get $get, Set $set) => self::updateTotals($get, $set)),

                Forms\Components\TextInput::make('debit_note_number')
                    ->required()
                    ->unique()
                    ->default(function () {
                        return 'DN-' . date('Y') . '-' . str_pad(DebitNote::count() + 1, 4, '0', STR_PAD_LEFT);
                    }),

                Forms\Components\DatePicker::make('issue_date')
                    ->required()
                    ->default(now()),

                Forms\Components\DatePicker::make('due_date')
                    ->required()
                    ->default(now()->addDays(30)),

                Forms\Components\Textarea::make('reason')
                    ->required()
                    ->rows(3)
                    ->placeholder('Reason for debit note (e.g., Additional charges, Correction, etc.)'),

                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->placeholder('Detailed description of the debit'),

                Forms\Components\Textarea::make('notes')
                    ->rows(3)
                    ->placeholder('Internal notes'),

                Forms\Components\Textarea::make('terms')
                    ->rows(3)
                    ->placeholder('Payment terms and conditions'),

                Forms\Components\Section::make('Debit Note Summary')
                    ->description('Financial totals and calculations')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Sub Total')
                            ->numeric()
                            ->readOnly()
                            ->prefixIcon('heroicon-o-currency-dollar'),
                        Forms\Components\TextInput::make('total_discount')
                            ->label('Total Discount')
                            ->numeric()
                            ->readOnly()
                            ->prefixIcon('heroicon-o-tag'),
                        Forms\Components\TextInput::make('tax_amount')
                            ->label('Total VAT')
                            ->numeric()
                            ->readOnly()
                            ->prefixIcon('heroicon-o-calculator'),
                        Forms\Components\TextInput::make('total')
                            ->numeric()
                            ->readOnly()
                            ->prefixIcon('heroicon-o-currency-dollar')
                            ->extraAttributes(['class' => 'font-bold text-lg']),
                    ])->columns(4)->columnSpan(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('debit_note_number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice.client.name_en')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('issue_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reason')
                    ->limit(50)
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('issue_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('issue_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('issue_date', '<=', $date),
                            );
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tenant_id', Auth::user()->id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDebitNotes::route('/'),
            'create' => Pages\CreateDebitNote::route('/create'),
            'edit' => Pages\EditDebitNote::route('/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $invoice = \App\Models\Invoice::find($data['invoice_id'] ?? null);
        if ($invoice) {
            $data['client_id'] = $invoice->client_id;
        }
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        $invoice = \App\Models\Invoice::find($data['invoice_id'] ?? null);
        if ($invoice) {
            $data['client_id'] = $invoice->client_id;
        }
        return $data;
    }

    public static function updateTotals(Get $get, Set $set): void
    {
        $invoiceService = app(InvoiceService::class);
        $items = $get('items') ?? [];
        $totals = $invoiceService->calculateTotals($items);

        $set('subtotal', number_format($totals['subtotal'], 2, '.', ''));
        $set('total_discount', number_format($totals['total_discount'], 2, '.', ''));
        $set('tax_amount', number_format($totals['total_tax'], 2, '.', ''));
        $set('total', number_format($totals['total'], 2, '.', ''));
    }
} 