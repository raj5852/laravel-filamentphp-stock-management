<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\HistoryTypeEnum;
use App\Models\Account;
use App\Models\History;
use App\Models\Purchase;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseShow extends Page implements HasActions, HasForms
{
    // use InteractsWithActions;
    use InteractsWithRecord;

    protected static string $resource = PurchaseResource::class;

    protected static string $view = 'filament.resources.purchase-resource.pages.purchase-show';

    public function mount(int|string $record): void
    {

        $this->record = $this->resolveRecord($record);
    }

    protected function getViewData(): array
    {
        return [
            'setting' => Setting::query()->first(),
            'purchase' => Purchase::query()
                ->with([
                    'histories',
                    'supplier:id,supplier_name,phone',
                    'purchaseitems' => function ($query) {
                        $query->select('id', 'product_id', 'purchase_id', 'total_in_text', 'rate', 'total_rate')
                            ->with('product:id,product_name,product_code');
                    }])
                ->find($this->record->id),

        ];
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->requiresConfirmation()
            ->action(function (array $arguments) {

                DB::transaction(function () use ($arguments) {

                    $history = History::findOrFail($arguments['id']);
                    $history->account()->increment('current_balance', $history->amount);
                    $history->purchase()->decrement('paid', $history->amount);
                    $history->purchase()->increment('due', $history->amount);
                    $history->delete();

                });

                Notification::make()
                    ->title('Deleted Successfully')
                    ->success()->send();

            });
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
                        'max_digits:10',
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
                    $purchase = Purchase::query()->findOrFail($arguments['id']);

                    $purchase->increment('paid', $data['amount']);
                    $purchase->decrement('due', $data['amount']);

                    $purchase->histories()->create([
                        'amount' => $data['amount'],
                        'account_id' => $data['account'],
                        'date' => $data['date'],
                        'note' => $data['note'],
                        'type' => HistoryTypeEnum::SPENT_OR_WITHDRAW->value,
                    ]);

                    Account::find($data['account'])->decrement('current_balance', $data['amount']);
                });

                Notification::make()->success()
                    ->title('Payment Added Successfully')
                    ->send();

            });
    }
}
