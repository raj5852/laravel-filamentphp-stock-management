<?php

namespace App\Filament\Pages;

use App\Models\Setting as ModelsSetting;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Setting extends Page implements HasForms
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static string $view = 'filament.pages.setting';

    protected static ?string $title = 'Settings';

    protected static ?string $navigationGroup = 'Setting & Customize';

    protected static ?int $navigationSort = 0;

    public $company_name;

    public $email_address;

    public $phone;

    public $address;

    public function mount(): void
    {
        $setting = ModelsSetting::query()->first();
        if ($setting) {
            $this->company_name = $setting->company_name;
            $this->email_address = $setting->email_address;
            $this->phone = $setting->phone;
            $this->address = $setting->address;
        }
    }

    public function getFormSchema(): array
    {
        return [
            Card::make([
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

        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();
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
