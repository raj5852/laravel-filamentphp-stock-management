<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\HistoryTypeEnum;
use App\Models\Account;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Actions\Action as FormsAction;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateNewPurchase extends Page implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static string $resource = PurchaseResource::class;

    protected static string $view = 'filament.resources.purchase-resource.pages.create-new-purchase';

    protected static ?string $title = 'Create  Purchase';

    public $supplier_id;

    public $purchase_date;

    public function __construct()
    {
        $this->purchase_date = now();
    }

    public $product_id;

    public $products = [];

    public $due;

    public $pay_amount = 0;

    public function getActions(): array
    {
        return [
            Action::make('add_supplier')
                ->label('Add Supplier')
                ->color(Color::Green)
                ->form([

                ]),

        ];
    }

    public function getFormSchema(): array
    {
        return [
            Card::make([
                Select::make('supplier_id')
                    ->label('Supplier')
                    ->options(Supplier::query()->pluck('supplier_name', 'id'))
                    ->searchable()
                    ->live()
                    ->rules([
                        Rule::exists('suppliers', 'id')->where('tenant_id', auth()->user()->tenant_id),
                    ])
                    ->createOptionForm([
                        TextInput::make('supplier_name')
                            ->placeholder('Enter Supplier Name')
                            ->autocomplete(false)
                            ->rules([
                                'required',
                                'string',
                                'min:0',
                                'max:256',
                            ])
                            ->required(),
                        TextInput::make('email')
                            ->placeholder('Enter Supplier Email')
                            ->rules([
                                'email',
                                'min:0',
                                'max:256',
                            ])
                            ->autocomplete(false)

                            ->email(),
                        Textarea::make('address')
                            ->placeholder('Write Supplier Address')
                            ->rules([
                                'string',
                                'min:0',
                                'max:5000',
                            ]),
                        TextInput::make('phone')
                            ->placeholder('Enter Supplier Phone')
                            ->autocomplete(false)
                            ->tel()
                            ->rules([
                                'required',
                                'string',
                                'min:0',
                                'max:256',
                            ])
                            ->required(),
                        TextInput::make('opening_receivable')
                            ->hidden(fn (string $context) => $context === 'edit')
                            ->rules([
                                'numeric',
                                'min:0',
                                'max:9999999999',
                            ])
                            ->placeholder('Opening Receivable')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('opening_payable')
                            ->hidden(fn (string $context) => $context === 'edit')
                            ->rules([
                                'numeric',
                                'min:0',
                                'max:9999999999',
                            ])
                            ->placeholder('Opening Payable')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->createOptionAction(function (FormsAction $action) {
                        $action
                            ->button()
                            // ->outlined()
                            ->icon('')
                            ->color(Color::Green)
                            ->modalWidth('md')
                            ->modalCancelAction(false)
                            ->label('Add Supplier');
                    })
                    ->createOptionModalHeading('Add Supplier')
                    ->createOptionUsing(function (array $data) {
                        $supplier = Supplier::create([
                            'supplier_name' => $data['supplier_name'],
                            'email' => $data['email'],
                            'address' => $data['address'],
                            'phone' => $data['phone'],
                            'opening_receivable' => $data['opening_receivable'] ?: 0,
                            'opening_payable' => $data['opening_payable'] ?: 0,
                        ]);

                        Notification::make()
                            ->title('Supplier created Successfully')
                            ->success()
                            ->send();

                        return $supplier->id;
                    })
                    ->required(),

                DatePicker::make('purchase_date')
                    ->label('Date')
                    ->native(false),

                Select::make('product_id')
                    ->label('Product')
                    ->reactive()
                    ->options(Product::query()->pluck('product_name', 'id'))
                    ->afterStateUpdated(function ($set, $get, $state) {
                        if ($get('supplier_id') == null) {
                            Notification::make()
                                ->danger()
                                ->title('Please Select Supplier First')
                                ->send();
                            $set('product_id', null);

                            return;
                        }

                        if (! is_null($state)) {

                            $products = collect($this->products);
                            $product = $products->where('id', $get('product_id'))->first();
                            if ($product) {

                                Notification::make()
                                    ->danger()
                                    ->title('Please increase the quantity of the items.')
                                    ->send();

                                $set('product_id', null);

                                return false;
                            }

                            $set('product_id', null);

                            $this->addProduct($state);
                            $set('product_id', null);

                        }

                    })
                    ->searchable(),
            ])->columns(2),
        ];
    }

    public function addProduct($productId)
    {
        $product = Product::find($productId)->load(['unit', 'subunit']);
        if ($product) {
            $this->products[] = [
                'id' => $product->id,
                'name' => $product->product_name,
                'rate' => $product->purchase_cost,

                'unit_id' => $product->unit_id,
                'sub_unit' => $product->sub_unit,

                'mainunit' => $product->unit,
                'subunit' => $product->subunit,

                'related_by_value' => $product->unit->related_by_value,

                'main_unit_qty' => null,
                'sub_unit_qty' => null,

                'sub_total' => $product->purchase_cost ?? 0,

            ];
        }
    }

    public function removeProduct($index)
    {
        unset($this->products[$index]);
        $this->products = array_values($this->products);
    }

    public function getGrandTotalProperty()
    {
        $grandTotal = 0;

        foreach ($this->products as $product) {
            $mainUnitPrice = ($product['rate'] ?: 0) * ($product['main_unit_qty'] ?: 0);
            $subUnitPrice = 0;

            if (! empty($product['subunit'])) {
                $singleSubUnitPrice = ($product['rate'] ?: 0) / $product['related_by_value'];
                $subUnitPrice = $singleSubUnitPrice * ($product['sub_unit_qty'] ?: 0);
            }

            $grandTotal += $mainUnitPrice + $subUnitPrice;
        }

        return $grandTotal;
    }

    public function totalDue($pay_amount = 0)
    {
        return number_format($this->getGrandTotalProperty() - ($pay_amount ?: 0), 2, '.', '');
    }

    public function paymentAction()
    {
        $accounts = Account::query()->pluck('name', 'id');

        return Action::make('Payment')
            ->form([
                Grid::make(2)
                    ->schema([
                        TextInput::make('paying_items')
                            ->label('Paying Items:')
                            ->numeric()
                            ->disabled()
                            ->default(collect($this->products)->count()),
                        TextInput::make('total_payable')
                            ->label('Total Payable:')
                            ->disabled()
                            ->default(function () {
                                return number_format($this->getGrandTotalProperty(), 2);
                            }),
                    ]),
                TextInput::make('due')
                    ->label('Due')
                    ->disabled()
                    ->default(function () {
                        return number_format($this->totalDue(), 2);
                    }),
                Textarea::make('note')
                    ->label('Note')
                    ->rules([
                        'nullable', 'max:5000',
                    ])
                    ->placeholder('Enter Note (Optional)'),
                Grid::make(2)
                    ->schema([

                        Select::make('account_id')
                            ->label('Transaction Account')
                            ->options($accounts->toArray()) // Convert the Collection to an array
                            ->default(array_key_first($accounts->toArray())) // Set the default to the first option
                            ->rules([
                                Rule::exists('accounts', 'id')->where('tenant_id', auth()->user()->tenant_id),
                            ])
                            ->required(),

                        TextInput::make('pay_amount')
                            ->debounce()
                            ->label('Pay Amount')
                            ->numeric()
                            ->placeholder('Pay Amount...')
                            ->rules(['nullable', 'numeric', 'min:0', 'max:9999999999'])
                            ->afterStateUpdated(function ($set, $get, $state) {
                                $set('due', $this->totalDue($state));
                            })
                            ->suffixAction(
                                FormsAction::make('paid')
                                    ->label('PAID!')
                                    ->color('success')
                                    ->icon('heroicon-s-check-circle')
                                    ->button()
                                    ->action(function ($set, $get) {

                                        $totalPayable = $this->getGrandTotalProperty();
                                        $set('pay_amount', number_format($totalPayable, 2, '.', ''));
                                        $set('due', 0);

                                    })
                            ),
                    ]),
            ])
            ->modalButton('Purchase')
            ->modalCancelAction(false)
            ->action(function (array $data) {

                // dd($this->products);
                $totalProduct = count($this->products);

                if ($totalProduct == 0) {
                    Notification::make()
                        ->danger()
                        ->title('Please add at least one product.')
                        ->send();

                    return;
                }

                $rules = [
                    '*.rate' => ['required', 'numeric', 'min:0', 'max:9999999999'],
                    '*.main_unit_qty' => ['nullable', 'integer', 'min:0', 'max:9999999999'],
                    '*.sub_unit_qty' => ['nullable', 'integer', 'min:0', 'max:9999999999'],
                    '*.id' => ['required', Rule::exists('products', 'id')->where('tenant_id', auth()->user()->tenant_id)],
                ];

                $validator = Validator::make($this->products, $rules);

                if ($validator->fails()) {
                    $errorMessages = implode(', ', $validator->errors()->all());

                    Notification::make()
                        ->danger()
                        ->title($errorMessages)
                        ->send();

                    return;
                }
                $supplier = Supplier::find($this->supplier_id);
                if (! $supplier) {
                    Notification::make()
                        ->danger()
                        ->title('Supplier not found')
                        ->send();

                    return;
                }

                try {
                    DB::beginTransaction();

                    $totalPurchase = Purchase::count() + 1;

                    $payable = number_format($this->getGrandTotalProperty(), 2, '.', '');
                    $paid = $data['pay_amount'] ?: 0;
                    $due = number_format($this->totalDue($data['pay_amount']), 2, '.', '');

                    $purchase = Purchase::create([
                        'billno' => $totalPurchase,
                        'supplier_id' => $this->supplier_id,
                        'purchase_date' => $this->purchase_date,
                        'payable' => $payable,
                        'paid' => $paid,
                        'due' => $due,
                        'note' => $data['note'],
                        // 'total_amount' => $payable + supplierDue($this->supplier_id),
                    ]);

                    foreach ($this->products as $product) {
                        $main_unit_qty = $product['main_unit_qty'] ?: 0;
                        $sub_unit_qty = $product['sub_unit_qty'] ?: 0;

                        $mainunitprice = ($product['rate'] ?: 0) * ($product['main_unit_qty'] ?: 0);
                        if ($product['subunit'] != '') {
                            $SingleSubunitPrice = ($product['rate'] ?: 0) / $product['related_by_value'];
                            $subunitPrice = $SingleSubunitPrice * ($product['sub_unit_qty'] ?: 0);
                        } else {
                            $subunitPrice = 0;
                        }

                        $total_subunitprice = number_format($mainunitprice + $subunitPrice, 2, '.', '');

                        $totalQty = getTotalStock($product['id'], $main_unit_qty, $sub_unit_qty);
                        $total_qty_in_text = getTotalStockInText($product['id'], $totalQty);

                        $getproduct = Product::find($product['id']);
                        $available_stock = $getproduct->productdetails->available_stock ?: 0;
                        $purchased = $getproduct->productdetails->purchased;

                        $getproduct->productdetails()->update([
                            'available_stock' => $available_stock + $totalQty,
                            'available_stock_in_text' => getTotalStockInText($product['id'], ($available_stock + $totalQty)),
                            'purchased' => $purchased + $totalQty,
                            'purchased_in_text' => getTotalStockInText($product['id'], ($purchased + $totalQty)),
                        ]);

                        $purchase->purchaseitems()->create([
                            'product_id' => $product['id'],
                            'rate' => $product['rate'],
                            'total_rate' => $total_subunitprice,
                            'main_unit_qty' => $product['main_unit_qty'],
                            'sub_unit_qty' => $product['sub_unit_qty'],
                            'total_qty' => $totalQty,
                            'total_in_text' => $total_qty_in_text,
                            'available_qty' => $totalQty,
                        ]);
                    }

                    if ($data['pay_amount'] != '') {
                        $account = Account::find($data['account_id']);
                        $account->decrement('current_balance', $data['pay_amount']);
                        $account->histories()->create([
                            'date' => $this->purchase_date,
                            'amount' => $data['pay_amount'],
                            'type' => HistoryTypeEnum::SPENT_OR_WITHDRAW->value,
                            'note' => '',
                            'purchase_id' => $purchase->id,
                            'supplier_id' => $this->supplier_id,

                        ]);
                    }

                    $this->products = [];

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();

                    // Handle exception

                }
                $this->products = [];

                return redirect()->route('filament.admin.resources.purchases.purchase-invoice', ['record' => $purchase->id]);

            });
    }
}
