<?php

namespace App\Filament\Resources\SalesResource\Pages;

use App\Filament\Resources\SalesResource;
use App\HistoryTypeEnum;
use App\Models\Account;
use App\Models\Customer;
use App\Models\History;
use App\Models\Order;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SalesShow extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SalesResource::class;

    protected static string $view = 'filament.resources.sales-resource.pages.sales-show';

    protected static ?string $title = '';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    protected function getViewData(): array
    {
        return [
            'setting' => Setting::query()->first(),
            'customer' => Customer::query()->withSum('orders', 'due')->find($this->record->customer_id),
            'order' => Order::query()->with('orderitems', 'histories')->find($this->record->id),
        ];
    }

    public function addpaymentAction(): Action
    {

        return Action::make('addpayment')
            ->label('Add Payment')
            ->icon('fas-plus')
            ->button()
            ->size('sm')
            ->outlined()
            ->color('success')
            ->modalHeading('Add Payment')
            ->form([
                DatePicker::make('date')
                    ->label('Payment Date')
                    ->native(false)
                    ->required()
                    ->default(now()),

                Select::make('account')
                    ->label('Transaction Account')
                    ->searchable()
                    ->options(Account::query()->pluck('name', 'id'))
                    ->rules([
                        'required', Rule::exists('accounts', 'id'),
                    ])
                    ->required(),

                TextInput::make('amount')
                    ->label('Amount')
                    ->required()
                    ->rules([
                        'required',
                        'min:0',
                        'max:9999999999',
                        'numeric',
                    ])
                    ->default(fn (array $arguments) => $arguments['amount'] ?? 0)
                    ->numeric(),

                Textarea::make('note')
                    ->rules([
                        'nullable', 'max:65535',

                    ])
                    ->label('note'),

            ])
            ->modalWidth('sm')
            ->modalHeading('Add Payment')
            ->modalButton('Add Payment')
            ->modalWidth('sm')
            ->mountUsing(function (array $arguments, $form) {
                $form->fill([
                    'amount' => $arguments['amount'] ?? 0,
                    'date' => now(),
                ]);
            })
            ->action(function (array $arguments, array $data) {
                DB::transaction(function () use ($arguments, $data) {
                    $order = Order::query()->findOrFail($arguments['id']);

                    $order->increment('paid', $data['amount']);
                    $order->decrement('due', $data['amount']);

                    $order->histories()->create([
                        'amount' => $data['amount'],
                        'account_id' => $data['account'],
                        'date' => $data['date'],
                        'note' => $data['note'],
                        'type' => HistoryTypeEnum::RECEIVED->value,
                        // 'total_amount' => customerDue($order->customer_id),
                        'customer_id' => $order->customer_id,
                    ]);

                    Account::find($data['account'])->decrement('current_balance', $data['amount']);
                });

                Notification::make()->success()
                    ->title('Payment Added Successfully')
                    ->send();

            });
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->requiresConfirmation()
            ->action(function (array $arguments) {

                DB::transaction(function () use ($arguments) {

                    $history = History::findOrFail($arguments['id']);
                    $history->account()->increment('current_balance', $history->amount);
                    $history->order()->decrement('paid', $history->amount);
                    $history->order()->increment('due', $history->amount);
                    $history->delete();

                });

                Notification::make()
                    ->title('Deleted Successfully')
                    ->success()->send();

            });
    }
}
