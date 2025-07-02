<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;
use App\Models\BusinessSetting;
use App\Models\Tax;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Illuminate\Validation\Rules\Unique;
use Modules\Accounting\Enums\FinancialAccountCategory;
use Modules\Accounting\Enums\FinancialAccountType;
use Modules\Accounting\Models\FinancialAccount;
use Modules\Accounting\Models\FinancialAccountTranslation;



use Filament\Forms\Contracts\HasForms;

class BusinessSettingPage extends Page implements HasForms
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $slug = 'settings';
    protected static string $view = 'filament.app.pages.business-setting-page';



    public ?array $data = [];
    public ?array $originalData = [];


    public function getHeading(): string
        {
            return __('Settings');
        }

    public function mount(): void
        {
            $data = BusinessSetting::pluck('value', 'key')->toArray();
            $taxes = Tax::get();
            $data['taxes'] = match ($taxes->isEmpty()) {
                true => [[
                    'name'       => '',
                    'percentage' => 0,
                    'included'   => false,
                ]],
            false => $taxes->toArray(),
        };


            $this->originalData = $data;

            $this->form->fill($data);
        }

     public function save(): void
    {
        /** @var Form $this->form */
        $data = collect($this->form->getState())->map(function ($value, $key) {
            return [
                'key'   => $key,
                'value' => json_encode($value),
            ];
        });

        $taxes = fluent($this->form->getState())->collect('taxes');
        $newTaxes = $taxes->filter(fn ($tax) => blank($tax['id']));
        $oldTaxes = $taxes->filter(fn ($tax) => ! blank($tax['id']));

        Tax::upsert($oldTaxes->toArray(), 'id');
        Tax::insert($newTaxes->toArray());
        Tax::whereNotIn('id', $taxes->pluck('id'))->delete();

        BusinessSetting::upsert(
            $data->except('taxes')->values()->toArray(),
            ['key'],
            ['value']
        );

        Notification::make()
            ->title(__('Saved Successfully'))
            ->success()
            ->send();
    }



    public function form(Form $form): Form
        {
            return $form
                ->statePath('data')
                ->schema([
                    Section::make('general')
                        ->columns(3)
                        ->heading(__('General'))
                        ->schema([
                            TextInput::make('english_company_name')
                                ->label(__('English Company Name'))
                                ->required(),
                            TextInput::make('arabic_company_name')
                                ->label(__('Arabic Company Name'))
                                ->required(),
                            TextInput::make('commercial_registration_number')
                                ->label(__('Commercial Registration Number'))
                                ->required(),
                            TextInput::make('vat_number')
                                ->label(__('VAT Number'))
                                ->required(),

                            FileUpload::make('logo')
                                ->image()
                                ->label(__('Logo')),
                        ]),

                    Section::make('business_information')
                        ->heading(__('Business Information'))
                        ->columns(3)
                        ->schema([
                            Fieldset::make(__('Location'))
                                ->columns(3)
                                ->schema([
                                    TextInput::make('business_country')
                                        ->label(__('Country'))
                                        ->required(),
                                    TextInput::make('business_city')
                                        ->label(__('City'))
                                        ->required(),
                                    Textarea::make('business_address')
                                        ->label(__('Address'))
                                        ->required(),
                                ]),
                            Fieldset::make(__('Contact'))
                                ->columns(3)
                                ->schema([
                                    TextInput::make('business_contact_name')
                                        ->label(__('Name'))
                                        ->required(),
                                    TextInput::make('business_contact_phone')
                                        ->label(__('Phone'))
                                        ->required(),
                                    TextInput::make('business_contact_email')
                                        ->label(__('Email'))
                                        ->required(),
                                ]),

                        ]),

                    Section::make('brand_information')
                        ->heading(__('Brand Information'))
                        ->columns(3)
                        ->schema([
                            Group::
                                make([
                                    TextInput::make('english_brand_name')
                                        ->label(__('English Name'))
                                        ->required(),
                                    TextInput::make('arabic_brand_name')
                                        ->label(__('Arabic Name'))
                                        ->required(),
                                ])
                                ->columns(3)
                                ->columnSpan(3),
                            Fieldset::make(__('Location'))
                                ->columns(3)
                                ->schema([
                                    Checkbox::make('location_same_as_business')
                                        ->label(__('Same As Above'))
                                        ->live()
                                        ->columnSpan(3)
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            if ($state) {
                                                $set('brand_country', $get('business_country'));
                                                $set('brand_city', $get('business_city'));
                                                $set('brand_address', $get('business_address'));
                                            }
                                        }),
                                    TextInput::make('brand_country')
                                        ->label(__('Country'))
                                        ->required()
                                        ->reactive(),
                                    TextInput::make('brand_city')
                                        ->label(__('City'))
                                        ->required()
                                        ->reactive(),
                                    Textarea::make('brand_address')
                                        ->label(__('Address'))
                                        ->required()
                                        ->reactive(),
                                ]),
                            Fieldset::make(__('Contact'))
                                ->columns(3)
                                ->schema([
                                    Checkbox::make('contact_same_as_business')
                                        ->label(__('Same As Above'))
                                        ->live()
                                        ->columnSpan(3)
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            if ($state) {
                                                $set('brand_contact_name', $get('business_contact_name'));
                                                $set('brand_contact_phone', $get('business_contact_phone'));
                                                $set('brand_contact_email', $get('business_contact_email'));
                                            }
                                        }),
                                    TextInput::make('brand_contact_name')
                                        ->label(__('Name'))
                                        ->required()
                                        ->reactive(),
                                    TextInput::make('brand_contact_phone')
                                        ->label(__('Phone'))
                                        ->required()
                                        ->reactive(),
                                    TextInput::make('brand_contact_email')
                                        ->label(__('Email'))
                                        ->required()
                                        ->reactive(),
                                ]),
                        ]),
                          Section::make('vat_information')
                    ->heading(__('Tax Information'))
                    ->schema([
                        Repeater::make('taxes')
                            ->label('VAT Types')
                            ->reorderable(false)
                            ->columns(3)
                            ->createItemButtonLabel(__('Add Tax'))
                            ->schema([
                                Hidden::make('id'),

                                TextInput::make('name')
                                    ->label(__('Name'))
                                    ->unique(
                                        'taxes',
                                        'name',
                                        modifyRuleUsing: fn (Unique $rule) => $rule->whereNotIn(
                                            'name',
                                            fluent($this->data)->get('taxes.*.name')
                                        )
                                    )
                                    ->required(),

                                TextInput::make('percentage')
                                    ->label(__('Percentage'))
                                    ->suffix('%')
                                    ->numeric()
                                    ->step('0.1')
                                    ->minValue(0)
                                    ->rules([
                                        "between:0,100",
                                    ])
                                    ->required(),

                                Toggle::make('included')
                                    ->label(__('Tax Included'))
                                    ->inline(false),
                            ]),
                    ]),

                    


                ]);
        }

    protected function getFormActions(): array
        {
            return [
                Action::make('save')
                    ->label(__('Save'))
                    ->size(ActionSize::Large)
                    ->submit('save'),
            ];
        }

}
