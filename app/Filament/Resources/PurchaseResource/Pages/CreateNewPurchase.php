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
                        'nullable', 'max:65535',
                    ])
                    ->placeholder('Enter Note (Optional)'),
                Grid::make(2)
                    ->schema([
                        Select::make('account_id')
                            ->label('Transaction Account')
                            ->options($accounts)
                            ->rules([
                                Rule::exists('accounts', 'id')->where('tenant_id', auth()->user()->tenant_id),
                            ])
                            ->required(),
                        TextInput::make('pay_amount')
                            ->debounce()
                            ->label('Pay Amount')
                            ->numeric()
                            ->placeholder('Pay Amount...')
                            ->rules(['nullable', 'numeric', 'min:0'])
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
                    '*.rate' => ['required', 'integer', 'min:0'],
                    '*.main_unit_qty' => ['nullable', 'integer'],
                    '*.sub_unit_qty' => ['nullable', 'integer'],
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
                    ]);
                }

                if ($data['pay_amount'] != '') {
                    $account = Account::find($data['account_id']);
                    $account->decrement('current_balance', $data['pay_amount']);
                    $account->histories()->create([
                        'date' => now(),
                        'amount' => $data['pay_amount'],
                        'type' => HistoryTypeEnum::SPENT_OR_WITHDRAW->value,
                        'note' => '',
                    ]);
                }


                return redirect()->route('filament.admin.resources.purchases.purchase-invoice', ['record' => $purchase->id]);

            });
    }
}
