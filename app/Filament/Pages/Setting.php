<?php

namespace App\Filament\Pages;

use App\Enums\InvoiceLogoType;
use App\Models\Setting as ModelsSetting;
use App\Models\User;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

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

    public $order_sms;

    public $invoice_design;

    public $timezone = 0;

    public static function canAccess(): bool
    {
        return auth()->user()->can('settings');
    }

    public function mount(): void
    {
        $setting = ModelsSetting::query()->first();
        if ($setting) {
            $authUser = auth()->user();
            $user = User::where('tenant_id', $authUser->tenant_id)->firstOrFail();

            $this->company_name = $setting->company_name;
            $this->email_address = $setting->email_address;
            $this->phone = $setting->phone;
            $this->address = $setting->address;
            $this->invoice_logo_type = $setting->invoice_logo_type;
            $this->low_stock_quantity = $setting->low_stock_quantity;
            $this->order_sms = $setting->order_sms;
            $this->invoice_design = $setting->invoice_design;
            $this->timezone = $user->timezone ?? 0;

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
                    'order_sms' => $this->order_sms,
                    'invoice_design' => $this->invoice_design,
                    'timezone' => $this->timezone,
                ]);
            }
        }
    }

    public function getFormSchema(): array
    {
        return [
            Tabs::make('Settings')
                ->tabs([
                    Tab::make('Company Info')
                        ->icon('heroicon-o-building-office')
                        ->schema([
                            Card::make([

                                FileUpload::make('file')
                                    ->label('Logo')
                                    ->image()
                                    ->imageEditor()
                                    ->visibility('public')
                                    ->rules(['image'])
                                    ->optimize('webp')
                                    ->imagePreviewHeight('180')
                                    ->maxSize(5120) // 5 MB
                                    ->resize(80),

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
                            ])->columns(2),
                            Card::make([
                                Radio::make('invoice_logo_type')
                                    ->label('Invoice Logo Type')
                                    ->inline()
                                    ->inlineLabel(false)
                                    ->options(InvoiceLogoType::class)
                                    ->default(InvoiceLogoType::LOGO),
                                Select::make('invoice_design')
                                    ->options([
                                        'a4' => 'A4',
                                        // 'pos_80mm' => 'POS 80mm',
                                    ])
                                    ->default('a4')
                                    ->rules([
                                        'required',
                                        'in:a4,pos_80mm',
                                    ])
                                    ->required(),
                            ])->columns(2),
                            Card::make([
                                TextInput::make('low_stock_quantity')
                                    ->label('Low Stock Quantity')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->maxValue(999999999999)
                                    ->placeholder('Enter low stock quantity')
                                    ->helperText('Product quantity threshold for low stock alert'),
                            ]),
                        ]),

                    Tab::make('Order SMS Settings')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->schema([
                            Card::make([
                                Placeholder::make('')
                                    ->content(new HtmlString('
                                        <div class="">
                                            <p><b>Customer Name: </b> {customer_name}</p>
                                            <p><b>Total Order Amount:</b> {amount} </p>
                                            <p><b>Bill No: </b> {bill_no}</p>
                                            <p><b>Order Date:</b> {order_date}</p>
                                            <p><b>Company Name:</b> {company_name}</p>
                                        </div>
                                    ')),
                                Textarea::make('order_sms')
                                    ->label('Order SMS')
                                    ->placeholder('Write your message here...')
                                    ->reactive()
                                    ->rows(5)
                                    ->required()
                                    ->helperText(fn ($state): string => 'Estimated SMS count: '.(empty($state) ? '0' : ceil(strlen($state) / 160))),
                            ]),
                        ]),
                    Tab::make('Timezone')
                        ->icon('heroicon-o-globe-alt')
                        ->schema([
                            Card::make([
                                Select::make('timezone')
                                    ->label('Timezone')
                                    ->searchable()
                                    ->options([
                                        '0' => 'Bangladesh (Asia/Dhaka)',
                                        '1' => 'Dubai (Asia/Dubai)',
                                    ])
                                    ->rules([
                                        'required',
                                        'in:0,1',
                                    ])
                                    ->default(0)
                                    ->required(),
                            ]),
                        ]),

                ]),
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

        // Save timezone to user
        $timezone = $data['timezone'];
        unset($data['timezone']);
        $authUser = Auth::user();
        User::where('tenant_id', $authUser->tenant_id)
            ->update(['timezone' => $timezone]);

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
