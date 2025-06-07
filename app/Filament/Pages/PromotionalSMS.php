<?php

namespace App\Filament\Pages;

use App\Models\Customer;
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
        return false;
    }

    public function getHeading(): string
    {
        return 'Send Promotional SMS';
    }

    public $customer_ids = [];

    public $message = '';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    Select::make('customer_ids')
                        ->label('Select Customer')
                        ->multiple()
                        ->options(Customer::query()->where('is_default', '!=', 1)->pluck('customer_name', 'id'))
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
                        ->required(),
                ]),
            ]);
    }

    public function submit()
    {
        // Validate the form data
        $data = $this->form->getState();

        // Process the SMS sending
        // This is where you would integrate with your SMS service
        // For example:
        // $customers = Customer::whereIn('id', $data['customer_ids'])->get();
        // foreach ($customers as $customer) {
        //     // Send SMS to customer->phone with $data['message']
        // }

        // Show success notification
        Notification::make()
            ->title('Not available in demo version')
            ->danger()
            ->send();

        // Reset the form
        $this->reset(['customer_ids', 'message']);
    }
}
