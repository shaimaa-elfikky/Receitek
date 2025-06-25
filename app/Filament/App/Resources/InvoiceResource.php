<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\InvoiceResource\Pages;
use App\Models\Client;
use App\Models\Invoice;
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
    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Group::make()->schema([
                Forms\Components\Section::make('Invoice Details')->schema([
                    Forms\Components\Select::make('client_id')
                        ->label('Client')
                        ->options(
                            Client::where('tenant_id', auth()->user()->id)
                                ->pluck('name_en', 'id')
                        )
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('invoice_number')
                        ->default(
                            'INV-' . str_pad(Invoice::max('id') + 1, 4, '0', STR_PAD_LEFT)
                        )
                        ->required(),
                    Forms\Components\DatePicker::make('issue_date')
                        ->default(now())
                        ->required(),
                    Forms\Components\DatePicker::make('due_date')
                        ->default(now()->addDays(14))
                        ->required(),
                ])->columns(2),

                Forms\Components\Section::make('Invoice Items')->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('description')
                                ->required()
                                ->columnSpan(2),
                            Forms\Components\TextInput::make('quantity')
                                ->required()
                                ->numeric()
                                ->default(1),
                            Forms\Components\TextInput::make('unit_price')
                                ->label('Unit Price')
                                ->required()
                                ->numeric(),
                            Forms\Components\Placeholder::make('total')
                                ->label('Line Total')
                                ->content(function (Get $get) {
                                    $total = $get('quantity') * $get('unit_price');
                                    return number_format($total, 2);
                                }),
                        ])
                        ->columns(5)
                        ->live() // This is crucial for the totals to update
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::updateTotals($get, $set);
                        })
                        ->deleteAction(function (Get $get, Set $set) {
                            self::updateTotals($get, $set);
                        }),
                ]),
            ])->columnSpan(['lg' => 2]),

            Forms\Components\Group::make()->schema([
                Forms\Components\Section::make('Status & Totals')->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'sent' => 'Sent',
                            'paid' => 'Paid',
                            'overdue' => 'Overdue',
                        ])
                        ->default('draft')
                        ->required(),
                    Forms\Components\TextInput::make('tax_rate')
                        ->label('Tax Rate (%)')
                        ->numeric()
                        ->default(0)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            self::updateTotals($get, $set);
                        }),
                    Forms\Components\TextInput::make('subtotal')
                        ->numeric()
                        ->readOnly(),
                    Forms\Components\TextInput::make('tax_amount')
                        ->numeric()
                        ->readOnly(),
                    Forms\Components\TextInput::make('total')
                        ->numeric()
                        ->readOnly(),
                ]),
                Forms\Components\Section::make('Notes')->schema([
                    Forms\Components\Textarea::make('notes'),
                    Forms\Components\Textarea::make('terms'),
                ]),
            ])->columnSpan(['lg' => 1]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->searchable(),
                Tables\Columns\TextColumn::make('client.name_en')->label('Client'),
                Tables\Columns\TextColumn::make('total')->money('SAR'),
                Tables\Columns\TextColumn::make('issue_date')->date(),
                Tables\Columns\TextColumn::make('status')->badge()->color(
                    fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'paid' => 'success',
                        'overdue' => 'danger',
                    }
                ),
            ])
            ->defaultSort('issue_date', 'desc');
    }

    // Helper function to calculate totals
    public static function updateTotals(Get $get, Set $set): void
    {
        $items = $get('items');
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }
        $set('subtotal', number_format($subtotal, 2, '.', ''));
        $taxRate = $get('tax_rate') ?? 0;
        $taxAmount = $subtotal * ($taxRate / 100);
        $set('tax_amount', number_format($taxAmount, 2, '.', ''));
        $set('total', number_format($subtotal + $taxAmount, 2, '.', ''));
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tenant_id', auth()->user()->id);
    }

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tenant_id'] = auth()->user()->id;
        return $data;
    }

    public static function getRelations(): array { return []; }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
