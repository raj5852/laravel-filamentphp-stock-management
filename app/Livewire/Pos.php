<?php

namespace App\Livewire;

use App\HistoryTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\ExpensePurchase;
use Filament\Actions\Action as LivewireAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action as ActionTable;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Pos extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?int $navigationSort = 1;

    public $order_date = null;

    public $customer_id = null;

    public $product_id = null;

    public $barcode = null;

    public $products = [];

    public $count = 0;

    public function __construct()
    {
        $this->order_date = now();
        $this->customer_id = Customer::first()?->id;
    }

    public function addProduct($productId)
    {
        $product = Product::findOrfail($productId)->load(['unit', 'subunit', 'productdetails:id,available_stock,product_id']);

        if ($product->productdetails->available_stock <= 0) {
            Notification::make()
                ->title('This product is Stock out. Please Purchases the Product.')
                ->danger()
                ->send();

            return;
        }
        if ($product) {
            $this->products[] = [
                'id' => $product->id,
                'name' => $product->product_name,
                'rate' => $product->sale_price,

                'available_stock' => $product->productdetails->available_stock,
                'product_code' => $product->product_code,

                'unit_id' => $product->unit_id,
                'sub_unit' => $product->sub_unit,

                'mainunit' => $product->unit,
                'subunit' => $product->subunit,

                'related_by_value' => $product->unit->related_by_value,

                'main_unit_qty' => null,
                'sub_unit_qty' => null,

                'sub_total' => $product->sale_price ?? 0,

            ];
        }
    }

    public function updateMainQuantity($index, $quantity)
    {
        $product = $this->products[$index];
        $stock = $product['available_stock'];

        $getQty = $this->getTotalStock($product['mainunit']['related_to_unit'], $product['related_by_value'], $quantity, $product['sub_unit_qty']);

        if ($stock < $getQty) {
            Notification::make()
                ->title('Not Enough Stock.')
                ->danger()
                ->send();

            $this->products[$index]['main_unit_qty'] = $this->getMainQty($product['mainunit']['related_to_unit'], $product['related_by_value'], $product['available_stock']);
            $this->products[$index]['sub_unit_qty'] = $this->getSubQty($product['related_by_value'], $product['available_stock']);
        }
    }

    public function getMainQty($related_to_unit, $related_by_value, $totalStockAmount)
    {
        if ($related_to_unit == '') {
            return $totalStockAmount;
        } else {
            return $getMainStock = (int) (($totalStockAmount ?: 0) / $related_by_value);
            // $getSubStock = ($totalStockAmount ?: 0) - ($related_by_value * $getMainStock);
        }
    }

    public function getSubQty($related_by_value, $totalStockAmount)
    {
        if ($related_by_value == 0) {
            return 0;
        }
        $getMainStock = (int) (($totalStockAmount ?: 0) / $related_by_value);

        return ($totalStockAmount ?: 0) - ($related_by_value * $getMainStock);
    }

    public function updateSubQuantity($index, $quantity)
    {
        $product = $this->products[$index];
        $stock = $product['available_stock'];

        $getQty = $this->getTotalStock($product['mainunit']['related_to_unit'], $product['related_by_value'], $product['main_unit_qty'], $quantity);

        if ($stock < $getQty) {
            Notification::make()
                ->title('Not Enough Stock.')
                ->danger()
                ->send();

            $this->products[$index]['main_unit_qty'] = $this->getMainQty($product['mainunit']['related_to_unit'], $product['related_by_value'], $product['available_stock']);
            $this->products[$index]['sub_unit_qty'] = $this->getSubQty($product['related_by_value'], $product['available_stock']);
        }
    }

    public function getTotalStock($related_to_unit, $related_by_value, $openingStockValue = null, $subOpeningStockValue = null)
    {
        if ($related_to_unit != '') {
            $relatedByValue = $related_by_value;
        } else {
            $relatedByValue = 1;
        }

        $totalMainUnit = ($openingStockValue ?: 0) * $relatedByValue;

        return $totalMainUnit + ($subOpeningStockValue ?: 0);
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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('barcode')
                    ->label('')
                    ->prefixIcon('fas-barcode')
                    ->placeholder('Scan Barcode')
                    ->autocomplete(false)
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {

                        $existsProducts = collect($this->products)->where('product_code', $state)->first();
                        if ($existsProducts) {
                            Notification::make()
                                ->title('Please Increase the quantity.')
                                ->danger()
                                ->send();
                            $set('barcode', null);

                            return;
                        }
                        $product = Product::where('product_code', $state)->first();
                        if ($product) {
                            $this->addProduct($product->id);
                            $set('barcode', null);

                            return;
                        }
                    }),
                Select::make('product_id')
                    ->label('')
                    ->searchable()
                    ->reactive()
                    ->options(Product::query()->pluck('product_name', 'id'))
                    ->native(false)
                    ->placeholder('Start to write product name...')
                    ->afterStateUpdated(function ($state, $set) {

                        $existsProducts = collect($this->products)->where('id', $state)->first();
                        if ($existsProducts) {
                            Notification::make()
                                ->title('Please Increase the quantity.')
                                ->danger()
                                ->send();
                            $set('product_id', null);

                            return;
                        }
                        $this->addProduct($state);
                        $set('product_id', null);
                        // dd($this->products);
                    }),
                DatePicker::make('order_date')
                    ->label('')
                    ->placeholder('Date')
                    ->native(false)
                    ->default($this->order_date),
                Select::make('customer_id')
                    ->label('')
                    ->placeholder('Select Customer')
                    ->searchable()
                    ->native(false)
                    ->options(Customer::query()->latest()->pluck('customer_name', 'id'))
                    ->createOptionForm([
                        TextInput::make('customer_name')
                            ->label('Name')
                            ->autocomplete(false)
                            ->rules([
                                'required',
                                'string',
                                'min:0',
                                'max:256',
                            ])
                            ->placeholder('Customer Name')
                            ->required(),

                        TextInput::make('email')
                            ->label('Email')
                            ->autocomplete(false)
                            ->email()
                            ->rules([
                                'nullable',
                                'string',
                                'min:0',
                                'max:256',
                                'email',
                            ])
                            ->placeholder('Email Address'),
                        Textarea::make('address')
                            ->label('Address')
                            ->autocomplete(false)
                            ->rules([
                                'nullable',
                                'string',
                                'min:0',
                                'max:5000',
                            ])
                            ->placeholder('Address'),

                        TextInput::make('phone')
                            ->label('Phone')
                            ->autocomplete(false)
                            ->required()
                            ->rules([
                                'required',
                                'string',
                                'min:0',
                                'max:256',
                            ])
                            ->placeholder('Phone Number'),

                        TextInput::make('opening_receivable')
                            ->label('Opening Receivable')
                            ->autocomplete(false)
                            ->rules([
                                'nullable',
                                'numeric',
                                'min:0',
                                'max:9999999999',
                            ])
                            ->placeholder('Opening Receivable'),

                        TextInput::make('opening_payable')
                            ->label('Opening Payable')
                            ->autocomplete(false)
                            ->rules([
                                'nullable',
                                'numeric',
                                'min:0',
                                'max:9999999999',
                            ])
                            ->placeholder('Opening Payable'),

                    ])
                    ->createOptionAction(function (Action $action) {
                        $action
                            ->button()
                            ->color(Color::Green)
                            ->icon('')
                            ->size('lg')
                            ->label('Add')
                            ->modalWidth('md')
                            ->modalCancelAction(false)
                            ->modalSubmitActionLabel('Add Customer');
                    })
                    ->createOptionModalHeading('Add Customer')
                    ->createOptionUsing(function ($data) {
                        $customer = Customer::create([
                            'customer_name' => $data['customer_name'],
                            'email' => $data['email'],
                            'address' => $data['address'],
                            'phone' => $data['phone'],
                            'opening_receivable' => $data['opening_receivable'] ?: 0,
                            'opening_payable' => $data['opening_payable'] ?: 0,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Customer Added Successfully')
                            ->send();

                        return $customer->id;
                    }),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->latest()->with('productdetails:id,product_id,available_stock_in_text'))
            ->columns([
                Split::make([
                    Stack::make([
                        ImageColumn::make('product_image')->defaultImageUrl('/images/notfound.jpg')->alignCenter(),
                        TextColumn::make('product_name')->getStateUsing(fn ($record) => $record->product_name.' - '.$record->product_code)->searchable(['product_name', 'product_code'])->alignCenter(),
                        TextColumn::make('sale_price')->getStateUsing(function ($record) {
                            return new HtmlString('<span class="font-bold">'.number_format($record->sale_price, 2, '.', '').'</span>'.' TK');
                        })->alignCenter(),
                        TextColumn::make('productdetails.available_stock_in_text')
                            ->getStateUsing(function ($record) {
                                return new HtmlString('<span >Stock: </span>'.$record->productdetails?->available_stock_in_text);
                            })
                            ->alignCenter(),
                    ]),

                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(Category::query()->pluck('name', 'id'))
                    ->placeholder('Select Category')
                    ->searchable(),
            ])
            ->recordAction('add_to_cart')
            ->actions([
                ActionTable::make('add_to_cart')
                    ->label('')
                    ->icon('')
                    ->action(function ($record) {

                        if (! $record) {
                            Notification::make()
                                ->title('Item not found!')
                                ->danger()
                                ->send();
                        }
                        $existsProducts = collect($this->products)->where('id', $record->id)->first();
                        if ($existsProducts) {
                            Notification::make()
                                ->title('Please Increase the quantity.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $this->addProduct($record->id);
                    }),

            ])
            ->paginated([12]);
    }

    public function totalDue($pay_amount = 0)
    {
        return number_format($this->getGrandTotalProperty() - ($pay_amount ?: 0), 2, '.', '');
    }

    public function paymentAction()
    {
        $accounts = Account::query()->pluck('name', 'id');

        return LivewireAction::make('Payment')
            ->form([
                Grid::make(2)
                    ->schema([
                        TextInput::make('paying_items')
                            ->label('Paying Items:')
                            ->numeric()
                            ->disabled()
                            ->default(collect($this->products)->count()),
                        TextInput::make('total_receivable')
                            ->label('Total Receivable:')
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
                        'nullable',
                        'max:5000',
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
                            ->placeholder('Amount')
                            ->rules(['nullable', 'numeric', 'min:0', 'max:9999999999'])
                            ->afterStateUpdated(function ($set, $get, $state) {
                                $set('due', $this->totalDue($state));
                            })
                            ->suffixAction(
                                Action::make('paid')
                                    ->label('PAID!')
                                    ->color('warning')
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
            ->modalButton('Order')
            ->modalCancelAction(false)
            ->modalWidth('md')
            ->action(function (array $data) {

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

                foreach ($this->products as $product) {
                    $getQty = $this->getTotalStock($product['mainunit']['related_to_unit'], $product['related_by_value'], $product['main_unit_qty'], $product['sub_unit_qty']);
                    if ($getQty > $product['available_stock']) {
                        Notification::make()
                            ->danger()
                            ->title('Some Products Does not Have stock!')
                            ->send();

                        return;
                    }
                }

                $customer = Customer::find($this->customer_id);
                if (! $customer) {
                    Notification::make()
                        ->danger()
                        ->title('Customer not found')
                        ->send();

                    return;
                }
                $due = number_format($this->totalDue($data['pay_amount']), 2, '.', '');

                if ($customer->is_default == 1 && $due != 0) {
                    Notification::make()
                        ->danger()
                        ->title('Walk-in Customer is do not support due. Please make Payment or Change Customer')
                        ->send();

                    return;
                }
                // dd($due, $data['pay_amount']);
                $receable = number_format($this->getGrandTotalProperty(), 2, '.', '');
                if ($receable < $data['pay_amount']) {
                    Notification::make()
                        ->danger()
                        ->title('Paid amount is greater than due amount')
                        ->send();

                    return;
                } else {

                    try {
                        DB::beginTransaction();

                        $totalOrder = Order::count() + 1;

                        $paid = $data['pay_amount'] ?: 0;

                        $order = Order::create([
                            'invoiceno' => $totalOrder,
                            'customer_id' => $this->customer_id,
                            'order_date' => $this->order_date,
                            'receivable' => $receable,
                            'paid' => $paid,
                            'due' => $due,
                            'note' => $data['note'],
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

                            $getproduct = Product::find($product['id'])->load('productdetails');
                            $available_stock = $getproduct->productdetails->available_stock ?: 0;
                            $sold = $getproduct->productdetails->sold;

                            $purchaseCost = $totalQty * $getproduct->productdetails->single_unit_purchase_price;
                            $getproduct->productdetails()->update([
                                'available_stock' => $available_stock - $totalQty,
                                'available_stock_in_text' => getTotalStockInText($product['id'], ($available_stock - $totalQty)),
                                'sold' => $sold + $totalQty,
                                'sold_in_text' => getTotalStockInText($product['id'], ($sold + $totalQty)),
                            ]);

                            $purchaseIds = ExpensePurchase::addPurchaseExpense($product['id'], $totalQty);

                            $order->orderitems()->create([
                                'product_id' => $product['id'],
                                'rate' => $product['rate'],
                                'total_rate' => $total_subunitprice,
                                'main_unit_qty' => $product['main_unit_qty'],
                                'sub_unit_qty' => $product['sub_unit_qty'],
                                'total_qty' => $totalQty,
                                'total_in_text' => $total_qty_in_text,
                                'available_qty' => $totalQty,
                                'purchase_cost' => $purchaseCost,
                                'purchase_ids' => $purchaseIds,
                            ]);
                        }
                        $orderDetails = $order->orderitems;
                        $order->update([
                            'profit' => ($orderDetails->sum('total_rate') ?: 0) - ($orderDetails->sum('purchase_cost') ?: 0),
                        ]);

                        $payment = Payment::create([
                            'customer_id' => $this->customer_id,
                            'payment_date' => $this->order_date,
                            'payment_type' => 'Cash Received',
                            'note' => $data['note'],
                        ]);

                        if ($data['pay_amount'] != '') {

                            $account = Account::find($data['account_id']);
                            $account->increment('current_balance', $data['pay_amount']);
                            $account->histories()->create([
                                'date' => today(),
                                'amount' => $data['pay_amount'],
                                'type' => HistoryTypeEnum::RECEIVED->value,
                                'order_id' => $order->id,
                                'customer_id' => $this->customer_id,
                                'payment_id' => $payment->id,
                            ]);
                        }

                        $this->customer_id = null;

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();

                        // Handle exception
                        Notification::make()
                            ->danger()
                            ->title('Something went wrong')
                            ->send();
                    }

                    Notification::make()
                        ->success()
                        ->title('Order Created Successfully')
                        ->send();

                    return to_route('filament.admin.resources.sales.pos-receipt', ['record' => $order->id]);
                }
            });
    }

    public function render()
    {
        return view('livewire.pos');
    }
}
