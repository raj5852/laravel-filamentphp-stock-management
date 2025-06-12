<?php

namespace App\Filament\Superadmin\Pages;

use App\Models\SmsApi as SmsApiModel;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SmsApi extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.superadmin.pages.sms-api';

    public ?array $data = [];

    public function mount(): void
    {
        $smsApi = SmsApiModel::first();

        if ($smsApi) {
            $this->form->fill([
                'api_key' => $smsApi->api_key,
                'sender_id' => $smsApi->sender_id,
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    TextInput::make('api_key')
                        ->label('SMS API Key')
                        ->placeholder('Enter your SMS API key')
                        ->required(),
                    TextInput::make('sender_id')
                        ->label('Sender ID')
                        ->placeholder('Sender Id')
                        ->required(),
                ]),

            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $smsApi = SmsApiModel::first();

        if ($smsApi) {
            $smsApi->update([
                'api_key' => $data['api_key'],
                'sender_id' => $data['sender_id'],
            ]);
        } else {
            SmsApiModel::create([
                'api_key' => $data['api_key'],
                'sender_id' => $data['sender_id'],
            ]);
        }

        Notification::make()
            ->title('SMS API key saved successfully')
            ->success()
            ->send();
    }
}
