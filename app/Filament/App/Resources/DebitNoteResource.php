<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\DebitNoteResource\Pages;
use App\Models\DebitNote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'name_en')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('invoice_id')
                    ->relationship('invoice', 'invoice_number')
                    ->searchable()
                    ->preload()
                    ->required(),

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

                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('$')
                    ->required()
                    ->minValue(0),

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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('debit_note_number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('client.name_en')
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
} 