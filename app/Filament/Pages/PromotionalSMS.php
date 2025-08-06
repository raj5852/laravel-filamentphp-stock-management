<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Setting;
use App\Models\User;
use App\Services\SmsService;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PromotionalSMS extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left';

    protected static string $view = 'filament.pages.promotional-s-m-s';

    protected static ?string $title = 'Promotional SMS';

    protected static ?string $slug = 'promotional-s-m-s';

    protected static ?string $navigationGroup = 'Promotion';

    

    public static function canAccess(): bool
    {
        return auth()->user()->can('promotional_sms');
    }

    public function getHeading(): string
    {
        return 'Send Promotional SMS';
    }

    public $customer_ids = [];
    public $customer_group_id = null;

    public $message = '';


    function mount(){
        // dd();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    Select::make('customer_group_id')
                        ->label('Select Customer Group')
                        ->options(CustomerGroup::query()->pluck('name', 'id'))
                        ->searchable()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if($state != ''){
                                $this->customer_ids = Customer::query()->where('customer_group_id', $state)->pluck('id')->toArray();
                            }else{
                                $this->customer_ids = [];
                            }
                        })
                        ->visible(Setting::first()?->is_customer_group == true)
                        ->live(),
                       
                    Select::make('customer_ids')
                        ->label('Select Customer')
                        ->multiple()
                        ->live()
                        ->options(
                            Customer::query()->where('is_default', '!=', 1)
                            ->pluck('customer_name', 'id')
                            )
                        ->searchable()
                        ->minItems(1)
                        ->hintAction(
                            Action::make('select_all')
                                ->label('Select All')
                                ->action(function (Select $component) {
                                    $component->state(
                                        Customer::query()->where('is_default', '!=', 1)->pluck('id')->toArray()
                                    );
                                })
                        )
                        ->required(),
                    Textarea::make('message')
                        ->label('SMS Body')
                        ->placeholder('Write your message here...')
                        ->required()
                        ->helperText(fn ($state): string => 'SMS count: '.(empty($state) ? '0' : ceil(strlen($state) / 160)))
                        ->reactive(),
                ]),
            ]);
    }

    public function submit()
    {
        // Validate the form data
        $data = $this->form->getState();
        $message = $data['message'];

        $user = User::find(auth()->user()->tenant_id);
        $userSms = $user->sms_count;

        $totalSms = ceil(strlen($message) / 160);
        $totalUser = count($data['customer_ids']);
        $grandTotal = $totalSms * $totalUser;

        if ($userSms < $grandTotal) {
            Notification::make()
                ->title('SMS Limit Exceeded')
                ->danger()
                ->send();

            return;
        } else {
            $numbers = Customer::query()->whereIn('id', $data['customer_ids'])->pluck('phone')->implode(',');

            SmsService::sendSms($numbers, $message);

            $user->decrement('sms_count', $grandTotal);

            // Show a success notification
            Notification::make()
                ->title('SMS Sent Successfully')
                ->success()
                ->send();
            // Reset the form
            $this->reset(['customer_ids', 'message']);
        }
    }
}
