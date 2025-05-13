<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\HistoryTypeEnum;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\ProductService;
use App\Services\PurchaseService;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Illuminate\Validation\Rule;

class AddPayment extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = PaymentResource::class;

    protected static string $view = 'filament.resources.payment-resource.pages.add-payment';

    public function mount()
    {
        $this->wallet_transaction = 0;
        $this->payment_date = today();
    }

    public $wallet_transaction;

    public $payment_date;

    public $payment_type;

    public $account_type;

    public $account_id;

    public $amount;

    public $transition_account;

    public $note;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    Select::make('wallet_transaction')
                        ->label('Wallet Transaction')
                        ->options([
                            0 => 'No',
                            1 => 'Yes',
                        ])
                        ->required()
                        ->rules([
                            'required',
                            'in:0,1',
                        ])
                        ->columnSpanFull(),

                    DatePicker::make('payment_date')
                        ->label('Payment Date')
                        ->required()
                        ->rules([
                            'date',
                        ])
                        ->native(false),

                    Select::make('payment_type')
                        ->label('Payment Type')
                        ->required()
                        ->options([
                            'cash_receive' => 'Cash Receive',
                            'cash_pay' => 'Cash Pay',
                        ])
                        ->rules([
                            'required',
                            'in:cash_receive,cash_pay',
                        ]),
                    Select::make('account_type')
                        ->label('Account Type')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($set) {
                            $set('account_id', []);
                        })
                        ->options([
                            'supplier' => 'Supplier',
                            'customer' => 'Customer',
                        ]),

                    Select::make('account_id')
                        ->label('Account ID')
                        ->required()
                        ->searchable()
                        ->options(function ($get) {
                            if ($get('account_type') == 'supplier') {
                                return Supplier::query()->latest()->pluck('supplier_name', 'id');
                            } elseif ($get('account_type') == 'customer') {
                                return Customer::query()->where('is_default', '!=', 1)->latest()->pluck('customer_name', 'id');
                            }
                        })
                        ->disabled(function ($get) {
                            if ($get('account_type') == null) {
                                return true;
                            }
                        }),

                    TextInput::make('amount')
                        ->label('Amount')
                        ->type('number')
                        ->required()
                        ->rules([
                            'required',
                            'numeric',
                            'min:0',
                            'max:9999999999',
                        ])
                        ->placeholder('Enter Amount'),

                    Select::make('transition_account')
                        ->label('Transition Account')
                        ->options(Account::query()->pluck('name', 'id'))
                        ->required()
                        ->rules([
                            'required',
                            Rule::exists('accounts', 'id'),
                        ])
                        ->searchable(),

                    Textarea::make('note')
                        ->placeholder('Write Note(optional)')
                        ->columnSpanFull(),

                ])
                    ->columns(2),
            ]);

        // ->statePath('data')
    }

    public function submit(): void
    {

        $data = $this->form->getState();

        // wallet transition yes
        if ($data['wallet_transaction'] == 1) {
            if ($data['account_type'] == 'customer') {

                $customer = Customer::query()->where('is_default', '!=', 1)->findOrFail($data['account_id']);

                if ($data['payment_type'] == 'cash_receive') {
                    $customer->update([
                        'wallet' => $customer->wallet + $data['amount'],
                    ]);
                }

                if ($data['payment_type'] == 'cash_pay') {
                    $customer->update([
                        'wallet' => $customer->wallet - $data['amount'],
                    ]);
                }

            }

            if ($data['account_type'] == 'supplier') {

                $supplier = Supplier::query()->findOrFail($data['account_id']);

                if ($data['payment_type'] == 'cash_receive') {
                    $supplier->update([
                        'wallet' => $supplier->wallet - $data['amount'],
                    ]);
                }

                if ($data['payment_type'] == 'cash_pay') {
                    $supplier->update([
                        'wallet' => $supplier->wallet + $data['amount'],
                    ]);
                }

            }
        }

        // wallet transition no
        if ($data['wallet_transaction'] == 0) {

            if ($data['account_type'] == 'customer') {

                $customer = Customer::query()->where('is_default', '!=', 1)
                    ->withSum('orders', 'due')
                    ->findOrFail($data['account_id']);

                $salesDue = $customer->orders_sum_due ?? 0;
                $balance = $data['amount'] - $salesDue ?? 0;

                $salesAmount = $data['amount'] - ($balance > 0 ? $balance : 0);

                if ($data['payment_type'] == 'cash_pay') {
                    $customer->update([
                        'wallet' => $customer->wallet - $data['amount'],
                    ]);
                }

                if ($data['payment_type'] == 'cash_receive') {

                    if ($balance > 0) {
                        $customer->update([
                            'wallet' => $customer->wallet + $balance,
                        ]);

                    }

                    $sales = ProductService::paidableSales($customer->id, $salesAmount);

                    // foreach ($sales as $pur) {
                    //     $purchase = Purchase::query()->findOrFail($pur['id']);

                    //     $purchase->increment('paid', $pur['due']);
                    //     $purchase->decrement('due', $pur['due']);

                    //     $purchase->histories()->create([
                    //         'amount' => $pur['due'],
                    //         'account_id' => $this->transition_account,
                    //         'date' => $this->payment_date,
                    //         'note' => '', // $this->note
                    //         'type' => HistoryTypeEnum::SPENT_OR_WITHDRAW->value,
                    //         'supplier_id' => $this->account_id,
                    //         'total_amount' => supplierDue($this->account_id),
                    //     ]);

                    //     Account::find($this->transition_account)->decrement('current_balance', $pur['due']);

                    // }
                }

            }

            if ($data['account_type'] == 'supplier') {

                $supplier = Supplier::query()
                    ->withSum('purchases', 'due')
                    ->findOrFail($data['account_id']);

                $purchaseDue = $supplier->purchases_sum_due ?? 0;

                $balance = ($data['amount'] - $purchaseDue) ?? 0;

                $purchaseAmount = $data['amount'] - ($balance > 0 ? $balance : 0);

                if ($data['payment_type'] == 'cash_receive') {
                    $supplier->update([
                        'wallet' => $supplier->wallet - $data['amount'],
                    ]);
                }

                if ($data['payment_type'] == 'cash_pay') {

                    if ($balance > 0) {

                        $supplier->update([
                            'wallet' => $supplier->wallet + $balance,
                        ]);

                    }

                    $purchases = PurchaseService::paidablePurchse($supplier->id, $purchaseAmount);

                    foreach ($purchases as $pur) {
                        $purchase = Purchase::query()->findOrFail($pur['id']);

                        $purchase->increment('paid', $pur['due']);
                        $purchase->decrement('due', $pur['due']);

                        $purchase->histories()->create([
                            'amount' => $pur['due'],
                            'account_id' => $this->transition_account,
                            'date' => $this->payment_date,
                            'note' => '', // $this->note
                            'type' => HistoryTypeEnum::SPENT_OR_WITHDRAW->value,
                            'supplier_id' => $this->account_id,
                            'total_amount' => supplierDue($this->account_id),
                        ]);

                        Account::find($this->transition_account)->decrement('current_balance', $pur['due']);

                    }
                }

            }

        }

    }
}
