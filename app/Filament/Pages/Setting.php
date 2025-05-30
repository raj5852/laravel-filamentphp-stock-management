<?php

namespace App\Filament\Pages;

use App\Enums\InvoiceLogoType;
use App\Models\Setting as ModelsSetting;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Setting extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static string $view = 'filament.pages.setting';

    protected static ?string $title = 'Settings';

    protected static ?string $navigationGroup = 'Setting & Customize';

    protected static ?int $navigationSort = 0;

    public $company_name;

    public $email_address;

    public $phone;

    public $address;

    public $file = [];  // Change from public $file; to public $file = [];

    public $invoice_logo_type = InvoiceLogoType::LOGO;

    public $low_stock_quantity;

    public function mount(): void
    {
        $setting = ModelsSetting::query()->first();
        if ($setting) {
            $this->company_name = $setting->company_name;
            $this->email_address = $setting->email_address;
            $this->phone = $setting->phone;
            $this->address = $setting->address;
            $this->invoice_logo_type = $setting->invoice_logo_type;
            $this->low_stock_quantity = $setting->low_stock_quantity;

            if ($setting->logo) {
                $this->file = $setting->logo;
                $this->form->fill([
                    'file' => $this->file,
                    'company_name' => $this->company_name,
                    'email_address' => $this->email_address,
                    'phone' => $this->phone,
                    'address' => $this->address,
                    'invoice_logo_type' => $this->invoice_logo_type,
                    'low_stock_quantity' => $this->low_stock_quantity,
                ]);
            }
        }
    }

    public function getFormSchema(): array
    {
        return [
            Card::make([
                FileUpload::make('file')
                    ->label('Logo')
                    ->image()
                    ->imageEditor()
                    ->visibility('public')
                    ->rules(['image'])
                    ->imagePreviewHeight('100')
                    ->maxSize(2048),
                TextInput::make('company_name')
                    ->label('Company Name')
                    ->required()
                    ->placeholder('Company Name')
                    ->rules([
                        'string',
                        'min:0',
                        'max:255',
                        'required',
                    ]),

                TextInput::make('email_address')
                    ->label('Email Address')
                    ->email()
                    ->required()
                    ->rules([
                        'required',
                        'email',
                        'max:255',
                    ])
                    ->placeholder('Email Address')
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('Phone')
                    ->required()
                    ->placeholder('Phone')
                    ->rules([
                        'string',
                        'min:0',
                        'max:255',
                        'required',
                    ])
                    ->maxLength(255),

                TextInput::make('address')
                    ->label('Address')
                    ->placeholder('Address')
                    ->rules([
                        'string',
                        'min:0',
                        'max:255',
                        'required',
                    ])
                    ->required(),

                // ColorPicker::make('color'),

            ])->columns(2),

            Card::make('Invoice Settings')
                ->schema([
                    Radio::make('invoice_logo_type')
                        ->label('Invoice Logo Type')
                        ->inline()
                        ->inlineLabel(false)
                        ->options(InvoiceLogoType::class)
                        ->default(InvoiceLogoType::LOGO),
                ]),

            Card::make('Other Settings')
                ->schema([
                    TextInput::make('low_stock_quantity')
                        ->label('Low Stock Quantity')
                        ->numeric()
                        ->minValue(1)
                        ->required()
                        ->placeholder('Enter low stock quantity')
                        ->helperText('Product quantity threshold for low stock alert')
                ])
        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        // Handle the file upload
        if (isset($data['file']) && ! empty($data['file'])) {
            $data['logo'] = is_array($data['file']) ? $data['file'][0] : $data['file'];
        }
        unset($data['file']);

        $setting = ModelsSetting::query()->first();
        if ($setting) {
            $setting->update($data);
        } else {
            ModelsSetting::create($data);
        }

        Notification::make()
            ->success()
            ->title('Settings Updated Successfully')
            ->send();
    }
}
