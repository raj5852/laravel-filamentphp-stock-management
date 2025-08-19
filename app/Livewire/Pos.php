<?php

namespace App\Livewire;

use App\HistoryTypeEnum;
use App\Models\Account;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\ExpensePurchase;
use App\Services\SmsService;
use Filament\Actions\Action as LivewireAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
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

    public $all_customers = [];

    public $oversale;
    public $is_customer_group = false;

    public function mount()
    {
        $setting = Setting::first();
        $this->customer_id = Customer::where('is_default', 1)->first()->id;
        $this->order_date = now();
        $this->oversale = $setting->oversale;
        $this->is_customer_group = $setting->is_customer_group;
    }

    public function addProduct($productId)
    {
        $product = Product::findOrfail($productId)->load(['unit', 'subunit', 'productdetails:id,available_stock,product_id']);

        if ($product->productdetails->available_stock <= 0 && $this->oversale == 0) {
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
                'discount_percentage' => 0,
                'available_stock' => $product->productdetails->available_stock,
                'product_code' => $product->product_code,
                'unit_id' => $product->unit_id,
                'sub_unit' => $product->sub_unit,
                'mainunit' => $product->unit,
                'subunit' => $product->subunit,
                'related_by_value' => $product->unit->related_by_value,
                'main_unit_qty' => 1,
                'sub_unit_qty' => 0,
                'sub_total' => $product->sale_price ?? 0,
                'has_varient' => $product->has_varient,
                'color_size' => $product->color_size,
                'user_color_size' => null,
            ];
        }
    }

    public function updateMainQuantity($index, $quantity)
    {
        $product = $this->products[$index];

        if ($product['has_varient'] == 1 && $product['user_color_size'] == '') {
            $this->products[$index]['main_unit_qty'] = 1;
            $this->products[$index]['sub_unit_qty'] = 0;
            Notification::make()
                ->title('Please select variation.')
                ->danger()
                ->send();

            return false;
        }

        if ($product['has_varient'] == 1) {
            $stock = collect($product['color_size'])->where('uniqid', $product['user_color_size'])->first()['available_stock'];
        } else {
            $stock = $product['available_stock'];
        }

        if ($this->oversale == 0) {
            $getQty = $this->getTotalStock($product['mainunit']['related_to_unit'], $product['related_by_value'], $quantity, $product['sub_unit_qty']);

            if ($stock < $getQty) {
                Notification::make()
                    ->title('Not Enough Stock.')
                    ->danger()
                    ->send();

                $this->products[$index]['main_unit_qty'] = $this->getMainQty($product['mainunit']['related_to_unit'], $product['related_by_value'], $stock);
                $this->products[$index]['sub_unit_qty'] = $this->getSubQty($product['related_by_value'], $stock);

                return;
            }
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
        if ($product['has_varient'] == 1 && $product['user_color_size'] == '') {
            $this->products[$index]['main_unit_qty'] = 1;
            $this->products[$index]['sub_unit_qty'] = 0;
            Notification::make()
                ->title('Please select variation.')
                ->danger()
                ->send();

            return false;
        }

        if ($product['has_varient'] == 1) {
            $stock = collect($product['color_size'])->where('uniqid', $product['user_color_size'])->first()['available_stock'];
        } else {
            $stock = $product['available_stock'];
        }

        $getQty = $this->getTotalStock($product['mainunit']['related_to_unit'], $product['related_by_value'], $product['main_unit_qty'], $quantity);

        if ($this->oversale == 0) {

            if ($stock < $getQty) {
                Notification::make()
                    ->title('Not Enough Stock.')
                    ->danger()
                    ->send();

                $this->products[$index]['main_unit_qty'] = $this->getMainQty($product['mainunit']['related_to_unit'], $product['related_by_value'], $stock);
                $this->products[$index]['sub_unit_qty'] = $this->getSubQty($product['related_by_value'], $stock);

                return;
            }
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
            $rate = is_numeric($product['rate']) ? (float) $product['rate'] : 0;
            $mainUnitQty = is_numeric($product['main_unit_qty']) ? (int) $product['main_unit_qty'] : 0;
            $mainUnitPrice = $rate * $mainUnitQty;
            $subUnitPrice = 0;

            if (! empty($product['subunit'])) {
                $singleSubUnitPrice = $rate / $product['related_by_value'];
                $subUnitQty = is_numeric($product['sub_unit_qty']) ? (int) $product['sub_unit_qty'] : 0;
                $subUnitPrice = $singleSubUnitPrice * $subUnitQty;
            }

            $subtotal = $mainUnitPrice + $subUnitPrice;
            $discountPercentage = is_numeric($product['discount_percentage']) ? (float) $product['discount_percentage'] : 0;
            $discount = ($subtotal * $discountPercentage) / 100;
            $final_subtotal = $subtotal - $discount;

            $grandTotal += $final_subtotal;
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

                        $existsProducts = collect($this->products)->where('has_varient', '!=', 1)->where('product_code', $state)->first();
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

                        $existsProducts = collect($this->products)->where('has_varient', '!=', 1)->where('id', $state)->first();
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
                    ->options(Customer::latest('id')->pluck('customer_name', 'id'))
                    ->default($this->customer_id)
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
                    Select::make('customer_group_id')
                    ->label('Customer Group')
                    ->visible($this->is_customer_group)
                    ->options(CustomerGroup::query()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Customer Group Name')
                                    ->autocomplete(false)
                                    ->rules([
                                        'required',
                                        'string',
                                        'min:0',
                                        'max:256',
                                    ])
                                    ->placeholder('Customer Group Name')
                                    ->required(),
                            ])
                            ->createOptionAction(function (Action $action) {
                                $action
                                    ->button()
                                    ->outlined()
                                    ->color(Color::Green)
                                    ->modalWidth('md')
                                    ->modalCancelAction(false)
                                    ->label('Add Customer Group');
                            })
                            ->createOptionUsing(function ($data) {
                                $customerGroup = CustomerGroup::create([
                                    'name' => $data['name'],
                                ]);
                                Notification::make()
                                    ->title('Customer Group Created')
                                    ->body('The Customer Group has been successfully added.')
                                    ->success()
                                    ->send();

                                return $customerGroup->id;
                            }),

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
                        $existsProducts = collect($this->products)->where('id', $record->id)->where('has_varient', '!=', 1)->first();
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

    public function totalDue($pay_amount = 0, $getDiscount = 0)
    {
        $mainbalance = $this->getGrandTotalProperty();
        $pay_amount = $pay_amount ?: 0;
        $val = $getDiscount ?: 0;

        if (preg_match('/^\d+(\.\d+)?%$/', $val)) {
            // If $val is a percentage
            $percentage = floatval(rtrim($val, '%')); // Remove the % sign and convert to float
            $discount = ($mainbalance * $percentage) / 100;
            $result = $mainbalance - $discount;
        } else {
            // If $val is a direct number
            $subtraction = floatval($val); // Convert to float
            $result = $mainbalance - $subtraction;
        }

        return number_format($result - $pay_amount, 2, '.', '');
    }

    public function paymentAction()
    {
        $accounts = Account::query()->where('is_active', true)->pluck('name', 'id');

        return LivewireAction::make('Payment')
            ->form([
                Card::make([
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
                    Grid::make(2)
                        ->schema([
                            TextInput::make('after_discount')
                                ->label('After Discount')
                                ->disabled()
                                ->default(function () {
                                    return number_format($this->getGrandTotalProperty(), 2);
                                }),
                            TextInput::make('due')
                                ->label('Due')
                                ->disabled()
                                ->default(function () {
                                    return number_format($this->totalDue(), 2);
                                }),
                        ]),
                ]),

                \Filament\Forms\Components\Section::make('More Options')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('discount')
                                    ->live(onBlur: false, debounce: 500)
                                    ->label('Discount')
                                    ->autocomplete(false)
                                    ->numeric(false)
                                    ->default('')
                                    ->placeholder('0%')
                                    ->rules(['nullable', 'regex:/^(\d+|\d+%)$/'])
                                    ->afterStateUpdated(function ($set, $get, $state) {
                                        $set('pay_amount', null);
                                        $total = $this->totalDue($get('pay_amount'), $state ?: 0);
                                        $set('pay_amount', null);
                                        $set('due', $total);
                                        $set('after_discount', $total);
                                    }),

                                Textarea::make('note')
                                    ->label('Note')
                                    ->rules([
                                        'nullable',
                                        'max:5000',
                                    ])
                                    ->placeholder('Enter Note (Optional)'),
                            ]),
                    ]),

                Grid::make(2)
                    ->schema([
                        Select::make('account_id')
                            ->label('Transaction Account')
                            ->options($accounts->toArray())
                            ->default(array_key_first($accounts->toArray()))
                            ->rules([
                                Rule::exists('accounts', 'id')->where('tenant_id', auth()->user()->tenant_id),
                            ])
                            ->required(),

                        TextInput::make('pay_amount')
                            ->live(onBlur: false, debounce: 500)
                            ->label('Pay Amount')
                            ->numeric()
                            ->placeholder('Amount')
                            ->rules(['nullable', 'numeric', 'min:0', 'max:9999999999'])
                            ->afterStateUpdated(function ($set, $get, $state) {
                                $set('due', $this->totalDue($state, $get('discount')));
                            })
                            ->suffixAction(
                                Action::make('paid')
                                    ->label('PAID!')
                                    ->color('warning')
                                    ->icon('heroicon-s-check-circle')
                                    ->button()
                                    ->action(function ($set, $get) {
                                        $bal_without_comma = str_replace(',', '', ($get('after_discount') ?: 0));
                                        $set('pay_amount', number_format($bal_without_comma, 2, '.', ''));
                                        $set('due', 0);
                                    })
                            ),
                    ]),

                Toggle::make('send_sms')
                    ->label('Send SMS'),

            ])
            ->modalButton('Order')
            ->modalCancelAction(false)
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

                // Additional validation for minimum quantity
                foreach ($this->products as $index => $product) {
                    $mainQty = is_numeric($product['main_unit_qty']) ? (int) $product['main_unit_qty'] : 0;
                    $subQty = is_numeric($product['sub_unit_qty']) ? (int) $product['sub_unit_qty'] : 0;

                    if ($mainQty === 0 && $subQty === 0) {
                        Notification::make()
                            ->danger()
                            ->title("Please add at least 1 quantity for product: {$product['name']}")
                            ->send();

                        return;
                    }
                    if ($product['has_varient'] == 1 && $product['user_color_size'] == '') {
                        Notification::make()
                            ->danger()
                            ->title("Please select color size for product: {$product['name']}")
                            ->send();

                        return;
                    }

                }

                if ($this->oversale == 0) {
                    foreach ($this->products as $product) {
                        $main_unit_qty = collect($this->products)->where('id', $product['id'])->where('user_color_size', $product['user_color_size'])->sum('main_unit_qty');
                        $sub_unit_qty = collect($this->products)->where('id', $product['id'])->where('user_color_size', $product['user_color_size'])->sum('sub_unit_qty');
                        // dd($main_unit_qty,$sub_unit_qty);

                        if ($product['has_varient'] == 1) {
                            $stock = collect($product['color_size'])->where('uniqid', $product['user_color_size'])->first()['available_stock'];
                        } else {
                            $stock = $product['available_stock'];
                        }

                        $getQty = $this->getTotalStock($product['mainunit']['related_to_unit'], $product['related_by_value'], $main_unit_qty, $sub_unit_qty);
                        if ($getQty > $stock) {
                            Notification::make()
                                ->danger()
                                ->title('Some Products Does not Have stock!')
                                ->send();

                            return;
                        }
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
                $due = number_format($this->totalDue($data['pay_amount'], $data['discount']), 2, '.', '');

                if ($customer->is_default == 1 && $due != 0) {
                    Notification::make()
                        ->danger()
                        ->title('Walk-in Customer is do not support due. Please make Payment or Change Customer')
                        ->send();

                    return;
                }

                $total_no_discount = number_format($this->getGrandTotalProperty(), 2, '.', '');

                $receable = $this->totalDue(0, $data['discount']);

                if ($due < 0) {
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
                            'discount' => $data['discount'],
                            'total_no_discount' => $total_no_discount,
                        ]);

                        foreach ($this->products as $product) {
                            $main_unit_qty = is_numeric($product['main_unit_qty']) ? (int) $product['main_unit_qty'] : 0;
                            $sub_unit_qty = is_numeric($product['sub_unit_qty']) ? (int) $product['sub_unit_qty'] : 0;
                            $rate = is_numeric($product['rate']) ? (float) $product['rate'] : 0;

                            $mainunitprice = $rate * $main_unit_qty;
                            if ($product['subunit'] != '') {
                                $SingleSubunitPrice = $rate / $product['related_by_value'];
                                $subunitPrice = $SingleSubunitPrice * $sub_unit_qty;
                            } else {
                                $subunitPrice = 0;
                            }

                            $subtotal = $mainunitprice + $subunitPrice;
                            $discountPercentage = is_numeric($product['discount_percentage']) ? (float) $product['discount_percentage'] : 0;
                            $item_discount = ($subtotal * $discountPercentage) / 100;
                            $final_subtotal = $subtotal - $item_discount;

                            $total_subunitprice = number_format($final_subtotal, 2, '.', '');

                            $totalQty = getTotalStock($product['id'], $main_unit_qty, $sub_unit_qty);

                            $purchaseIds = ExpensePurchase::addPurchaseExpense($product['id'], $totalQty, $product['user_color_size']);
                            $over_sale_qty = (int) $totalQty - (int) collect($purchaseIds)->sum('qty');

                            $totalPurcahseCost = collect($purchaseIds)->sum('purchase_value');

                            $total_qty_in_text = getTotalStockInText($product['id'], $totalQty);

                            $getproduct = Product::find($product['id'])->load('productdetails');
                            $available_stock = $getproduct->productdetails->available_stock ?: 0;
                            $sold = $getproduct->productdetails->sold;

                            $getproduct->productdetails()->update([
                                'available_stock' => $available_stock - $totalQty,
                                'available_stock_in_text' => getTotalStockInText($product['id'], ($available_stock - $totalQty)),
                                'sold' => $sold + $totalQty,
                                'sold_in_text' => getTotalStockInText($product['id'], ($sold + $totalQty)),
                            ]);

                            $order->orderitems()->create([
                                'product_id' => $product['id'],
                                'rate' => $product['rate'],
                                'total_rate' => $total_subunitprice,
                                'main_unit_qty' => $product['main_unit_qty'] ?: 0,
                                'sub_unit_qty' => $product['sub_unit_qty'] ?: 0,
                                'total_qty' => $totalQty,
                                'total_in_text' => $total_qty_in_text,
                                'available_qty' => $totalQty,
                                'purchase_cost' => $totalPurcahseCost,
                                'purchase_ids' => $purchaseIds,
                                'discount_percentage' => $product['discount_percentage'] ?: 0,
                                'discount_amount' => $item_discount,
                                'over_sale_qty' => $over_sale_qty,
                                'varient_uniqid' => $product['user_color_size'],
                            ]);

                            if ($product['has_varient'] == 1) {
                                updateProductVarient($product['id'], $product['user_color_size'], available_stock: -$totalQty);
                            }

                        }
                        $orderDetails = $order->orderitems;
                        $order->update([
                            'profit' => ($receable ?: 0) - ($orderDetails->sum('purchase_cost') ?: 0),
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


                        if ($data['send_sms'] && ($customer->is_default != 1)) {
                            // $smsCount = User::where('tenant_id', auth()->user()->tenant_id)->first()?->sms_count ?? 0;

                            $setting = Setting::first();
                            $message = $setting->order_sms;

                            $user = User::find(auth()->user()->tenant_id);
                            $userSms = $user->sms_count;

                            $customer_name = $customer->customer_name;
                            $amount = $receable;
                            $order_date = $order->order_date;
                            $bill_no = $order->invoiceno;
                            $company_name = $setting->company_name;
                            $paid_amount = $data['pay_amount'] ?? 0;
                            $total_due = getTotalDue($this->customer_id);

                            $replacements = [
                                '{customer_name}' => $customer_name,
                                '{amount}' => $amount,
                                '{order_date}' => Carbon::parse($order_date)->format('Y-m-d'),
                                '{bill_no}' => $bill_no,
                                '{company_name}' => $company_name,
                                '{paid_amount}' => $paid_amount,
                                '{total_due}'=> $total_due,
                            ];

                            foreach ($replacements as $key => $value) {
                                if (Str::contains($message, $key)) {
                                    $message = str_replace($key, $value, $message);
                                }
                            }

                            $finalMessage = $message;

                            $totalSms = ceil(strlen($finalMessage) / 160);

                            if ($totalSms <= $userSms) {
                                $data = SmsService::sendSms($customer->phone, $finalMessage);

                                $user->decrement('sms_count', $totalSms);
                            } else {
                                Notification::make()
                                    ->danger()
                                    ->title('SMS Limit Exceeded')
                                    ->send();
                            }
                        }

                        $this->customer_id = null;

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();

                        // Log the exception
                        Log::error($e);
                        throw $e;

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

                    return to_route('filament.admin.pages.pos', ['invoices' => $order->id]);
                }
            });
    }

    public function render()
    {
        return view('livewire.pos');
    }
}
