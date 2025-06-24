<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Filament\Resources\SubscriptionResource\RelationManagers;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Membership Management';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Select::make('tenant_id')
                    ->label('Tenant')
                    ->relationship('tenant', 'company_name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\DatePicker::make('start_date')
                    ->required()
                    ->default(now()),

                Forms\Components\DatePicker::make('end_date')->required(),

                Forms\Components\TextInput::make('extra_invoices')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Forms\Components\TextInput::make('extra_users')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Forms\Components\Toggle::make('status')
                    ->label('Active')
                    ->required()
                    ->default(true)
                    ->onColor('success')
                    ->offColor('danger'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.company_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('plan.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')->date()->sortable(),
                   Tables\Columns\IconColumn::make('status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plan')->relationship(
                    'plan',
                    'name'
                ),
                Tables\Filters\SelectFilter::make('status')->options([
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                    'expired' => 'Expired',
                    'cancelled' => 'Cancelled',
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
            ]);
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
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'view' => Pages\ViewSubscription::route('/{record}'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}