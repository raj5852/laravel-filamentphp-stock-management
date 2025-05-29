<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\HistoryTypeEnum;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\ProductService;
use App\Services\PurchaseService;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
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
                        ->live()
                        ->options(function ($get) {
                            if ($get('account_type') == 'supplier') {
                                return Supplier::query()->where('is_default', '!=', 1)->latest()->pluck('supplier_name', 'id');
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
                        ->autocomplete(false)
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

                    Placeholder::make('')
                        ->content(function ($get) {
                            if ($get('account_id') != null && $get('account_type') == 'customer') {
                                $customer = Customer::find($get('account_id'));
                                $dueInvoiceCount = $customer->orders()->where('due', '>', 0)->count();
                                $totalInvoiceDue = $customer->orders()->sum('due');

                                $message = '';

                                if ($customer->wallet < 0) {
                                    $message = '<span>**কাস্টমারের কাছে আপনার </span><span>পাওনা রয়েছে</span>';
                                } elseif ($customer->wallet >= 0) {
                                    $message = '**** কাস্টমারের ওয়ালেটে জমা আছেঃ ';
                                }

                                // balance
                                $balance = 0;

                                if ($customer->wallet <= 0) {
                                    $balance = abs($customer->wallet);
                                } else {
                                    $balance = 0;
                                }

                                return new HtmlString('
                                    <p><b>Name:  ' . $customer->customer_name . '</b> </p>
                                    <p><b>Due Invoice Count:  ' . $dueInvoiceCount . '</b></p>
                                    <p><b>Total Invoice Due:  ' . $totalInvoiceDue . ' TK</b> *** বিক্রয় বাবদ পাওনা আছে ' . $totalInvoiceDue . ' Tk *** </p>
                                    <p><b>Wallet Balance: ' . number_format(abs($customer->wallet), 1) . ' TK</b> ' . $message . ' ' . abs($customer->wallet) . ' Tk **** </p>
                                    <p><b>Total Due: ' . $balance + $totalInvoiceDue . ' TK </b> </p>
                                ');
                            }

                            if ($get('account_id') != null && $get('account_type') == 'supplier') {
                                $supplier = Supplier::find($get('account_id'));
                                $dueInvoiceCount = $supplier->purchases()->where('due', '>', 0)->count();
                                $totalInvoiceDue = $supplier->purchases()->sum('due');

                                $message = '';

                                if ($supplier->wallet > 0) {
                                    $message = '<span >*** সাপ্লাইয়ারের কাছে জমা আছেঃ </span>';
                                } elseif ($supplier->wallet <= 0) {
                                    $message = '<span >*** আপনার থেকে সাপ্লাইয়ার পাবেঃ</span>';
                                }

                                // balance
                                $balance = 0;

                                if ($supplier->wallet <= 0) {
                                    $balance = abs($supplier->wallet);
                                } else {
                                    $balance = 0;
                                }

                                return new HtmlString('
                                <p><b>Name:  ' . $supplier->supplier_name . '</b> </p>
                                <p><b>Due Invoice Count:  ' . $dueInvoiceCount . '</b></p>
                                <p><b>Total Invoice Due:  ' . $totalInvoiceDue . ' TK</b> *** ক্রয় বাবদ দেনা আছে ' . $totalInvoiceDue . ' Tk *** </p>
                                <p><b>Wallet Balance: ' . number_format(abs($supplier->wallet), 1) . ' TK</b> ' . $message . ' ' . abs($supplier->wallet) . ' Tk **** </p>
                                <p><b>Total Due: ' . number_format($balance + $totalInvoiceDue, 2) . ' TK </b> </p>
                            ');
                            }
                        }),

                    Textarea::make('note')
                        ->placeholder('Write Note(optional)')
                        ->rules([
                            'string',
                            'max:5000',
                        ])
                        ->columnSpanFull(),

                ])
                    ->columns(2),
            ]);

        // ->statePath('data')
    }

    public function submit(): void
    {

        $data = $this->form->getState();

        try {
            DB::beginTransaction();

            // wallet transition yes
            if ($data['wallet_transaction'] == 1) {
                if ($data['account_type'] == 'customer') {

                    $customer = Customer::query()->where('is_default', '!=', 1)->findOrFail($data['account_id']);

                    if ($data['payment_type'] == 'cash_receive') {
                        $customer->update([
                            'wallet' => $customer->wallet + $data['amount'],
                        ]);

                        $payment = Payment::create([
                            'customer_id' => $this->account_id,
                            'payment_date' => $this->payment_date,
                            'payment_type' => 'Cash Received',
                            'note' => $data['note'],
                        ]);

                        $customer->histories()->create([
                            'amount' => $data['amount'],
                            'account_id' => $this->transition_account,
                            'date' => today(),
                            'type' => HistoryTypeEnum::RECEIVED->value,
                            'payment_id' => $payment->id,
                            'is_wallet_transaction' => 1,
                        ]);

                        Account::find($this->transition_account)->increment('current_balance', $data['amount']);
                    }

                    if ($data['payment_type'] == 'cash_pay') {
                        $customer->update([
                            'wallet' => $customer->wallet - $data['amount'],
                        ]);

                        $payment = Payment::create([
                            'customer_id' => $this->account_id,
                            'payment_date' => $this->payment_date,
                            'payment_type' => 'Cash Pay',
                            'note' => $data['note'],
                        ]);

                        $customer->histories()->create([
                            'amount' => $data['amount'],
                            'account_id' => $this->transition_account,
                            'date' => today(),
                            'type' => HistoryTypeEnum::SPENT_OR_WITHDRAW->value,
                            'payment_id' => $payment->id,
                            'is_wallet_transaction' => 1,
                        ]);

                        Account::find($this->transition_account)->decrement('current_balance', $data['amount']);
                    }
                }

                if ($data['account_type'] == 'supplier') {

                    $supplier = Supplier::query()->findOrFail($data['account_id']);

                    if ($data['payment_type'] == 'cash_receive') {
                        $supplier->update([
                            'wallet' => $supplier->wallet - $data['amount'],
                        ]);

                        $payment = Payment::create([
                            'supplier_id' => $this->account_id,
                            'payment_date' => $this->payment_date,
                            'payment_type' => 'Cash Received',
                            'note' => $data['note'],
                        ]);

                        $supplier->histories()->create([
                            'amount' => $data['amount'],
                            'account_id' => $this->transition_account,
                            'date' => today(),
                            'type' => HistoryTypeEnum::RECEIVED->value,
                            'payment_id' => $payment->id,
                            'is_wallet_transaction' => 1,
                        ]);

                        Account::find($this->transition_account)->increment('current_balance', $data['amount']);
                    }

                    if ($data['payment_type'] == 'cash_pay') {
                        $supplier->update([
                            'wallet' => $supplier->wallet + $data['amount'],
                        ]);

                        $payment = Payment::create([
                            'supplier_id' => $this->account_id,
                            'payment_date' => $this->payment_date,
                            'payment_type' => 'Cash Pay',
                            'note' => $data['note'],
                        ]);

                        $supplier->histories()->create([
                            'amount' => $data['amount'],
                            'account_id' => $this->transition_account,
                            'date' => today(),
                            'type' => HistoryTypeEnum::SPENT_OR_WITHDRAW->value,
                            'payment_id' => $payment->id,
                            'is_wallet_transaction' => 1,
                        ]);

                        Account::find($this->transition_account)->decrement('current_balance', $data['amount']);
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

                        $payment = Payment::create([
                            'customer_id' => $this->account_id,
                            'payment_date' => $this->payment_date,
                            'payment_type' => 'Cash Pay',
                            'note' => $data['note'],

                        ]);

                        $customer->histories()->create([
                            'amount' => $data['amount'],
                            'account_id' => $this->transition_account,
                            'date' => today(),
                            'type' => HistoryTypeEnum::SPENT_OR_WITHDRAW->value,
                            'payment_id' => $payment->id,
                            'is_wallet_transaction' => 1,
                        ]);

                        Account::find($this->transition_account)->decrement('current_balance', $data['amount']);
                    }

                    if ($data['payment_type'] == 'cash_receive') {

                        $payment = Payment::create([
                            'customer_id' => $this->account_id,
                            'payment_date' => $this->payment_date,
                            'payment_type' => 'Cash Received',
                            'note' => $data['note'],
                        ]);

                        if ($balance > 0) {
                            $customer->update([
                                'wallet' => $customer->wallet + $balance,
                            ]);

                            $customer->histories()->create([
                                'amount' => $balance,
                                'account_id' => $this->transition_account,
                                'date' => today(),
                                'type' => HistoryTypeEnum::RECEIVED->value,
                                'payment_id' => $payment->id,
                                'is_wallet_transaction' => 1,
                            ]);

                            Account::find($this->transition_account)->increment('current_balance', $balance);
                        }

                        $sales = ProductService::paidableSales($customer->id, $salesAmount);

                        foreach ($sales as $sale) {

                            $order = Order::query()->findOrFail($sale['id']);

                            $order->increment('paid', $sale['due']);
                            $order->decrement('due', $sale['due']);

                            $order->histories()->create([
                                'amount' => $sale['due'],
                                'account_id' => $this->transition_account,
                                'date' => today(),
                                'type' => HistoryTypeEnum::RECEIVED->value,
                                'customer_id' => $order->customer_id,
                                'payment_id' => $payment->id,
                            ]);

                            Account::find($this->transition_account)->increment('current_balance', $sale['due']);
                        }
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

                        $payment = Payment::create([
                            'supplier_id' => $this->account_id,
                            'payment_date' => $this->payment_date,
                            'payment_type' => 'Cash Received',
                            'note' => $data['note'],
                        ]);

                        $supplier->histories()->create([
                            'amount' => $data['amount'],
                            'account_id' => $this->transition_account,
                            'date' => today(),
                            'type' => HistoryTypeEnum::RECEIVED->value,
                            'payment_id' => $payment->id,
                            'is_wallet_transaction' => 1,

                        ]);

                        Account::find($this->transition_account)->increment('current_balance', $data['amount']);
                    }

                    if ($data['payment_type'] == 'cash_pay') {

                        $payment = Payment::create([
                            'supplier_id' => $this->account_id,
                            'payment_date' => $this->payment_date,
                            'payment_type' => 'Cash Pay',
                            'note' => $data['note'],
                        ]);

                        if ($balance > 0) {

                            $supplier->update([
                                'wallet' => $supplier->wallet + $balance,
                            ]);

                            $supplier->histories()->create([
                                'amount' => $balance,
                                'account_id' => $this->transition_account,
                                'date' => today(),
                                'type' => HistoryTypeEnum::SPENT_OR_WITHDRAW->value,
                                'payment_id' => $payment->id,
                                'is_wallet_transaction' => 1,
                            ]);

                            Account::find($this->transition_account)->decrement('current_balance', $balance);
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
                                'type' => HistoryTypeEnum::SPENT_OR_WITHDRAW->value,
                                'supplier_id' => $this->account_id,
                                'payment_id' => $payment->id,

                            ]);

                            Account::find($this->transition_account)->decrement('current_balance', $pur['due']);
                        }
                    }
                }
            }

            Notification::make()
                ->title('Payment Added Successfully')
                ->success()
                ->send();
            $this->wallet_transaction = null;
            redirect()->route('filament.admin.resources.payments.index');
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }
}
