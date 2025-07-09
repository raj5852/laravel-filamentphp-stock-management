<?php

namespace App\Filament\Resources;

use App\Filament\Exports\SalesExporter;
use App\Filament\Resources\SalesResource\Pages;
use App\HistoryTypeEnum;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\ReturnList;
use App\Models\ReturnListProduct;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SalesResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'fas-bag-shopping';

    protected static ?string $navigationGroup = 'Sale & Purchase';

    protected static ?string $pluralModelLabel = 'Sales';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Order::query()
                ->with([
                    'orderitems:id,product_id,order_id,total_in_text',
                    'orderitems.product:id,product_name,product_code',
                    'returnlist',
                ])
                ->withSum('orderitems', 'purchase_cost')
                ->latest())
            ->columns([
                TextColumn::make('invoiceno'),
                TextColumn::make('customer.customer_name'),
                TextColumn::make('orderitems')->label('Items')
                    ->formatStateUsing(function ($record) {
                        // Fetch related orderitems with product details
                        $items = $record->orderitems->map(function ($purchaseItem) {
                            // dd($purchaseItem);
                            $productName = $purchaseItem->product->product_name ?? 'N/A';
                            // $productCode = $purchaseItem->product->product_code ?? 'N/A';

                            return "{$productName} * {$purchaseItem->total_in_text}"; // Format: "Product Name (Product Code)"
                        })->toArray();

                        // Format as a list (ul > li)
                        return '<ul class="list-disc pl-5 space-y-2">'.implode('', array_map(fn ($item) => "<li class='max-w-[300px] whitespace-normal'>{$item}</li>", $items)).'</ul>';
                    })
                    ->html(),
                TextColumn::make('order_date')->label('Date')->date(),
                TextColumn::make('discount')->label('Discount')
                    ->getStateUsing(function ($record) {
                        return number_format(($record->total_no_discount + $record->returnlist->total_no_discount) - ($record->receivable + $record->returnlist->receivable)).' TK';
                    }),

                TextColumn::make('receivable')->formatStateUsing(function ($record) {
                    return number_format($record->receivable + $record->returnlist->receivable, 2).' TK';
                }),
                TextColumn::make('paid')->formatStateUsing(function ($record) {
                    return number_format($record->paid, 2).' TK';
                }),

                TextColumn::make('product_returned')
                    ->getStateUsing(function ($record) {
                        return number_format($record->product_returned ?: 0, 0).' Tk';
                    })
                    ->label('Product Returned'),

                TextColumn::make('due')->formatStateUsing(function ($record) {
                    return number_format($record->due - $record->returnlist->paid, 2).' TK';
                }),
                TextColumn::make('orderitems_sum_purchase_cost')->label('Purchase Cost')->formatStateUsing(function ($record) {
                    return number_format($record->orderitems_sum_purchase_cost, 2).' TK';
                }),
                TextColumn::make('Profit')->default(function ($record) {

                    return number_format($record->profit, 2).' Tk';
                }),
                TextColumn::make('Status')->default(function ($record) {
                    return ($record->due - $record->returnlist->paid) > 0 ? 'Unpaid' : 'Paid';
                }),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(SalesExporter::class)
                    ->columnMapping(false)
                    ->label('Export')
                    ->icon('fas-download')
                    ->modifyQueryUsing(function (Builder $query) {
                        // Get the current filter values from the request
                        $invoiceNo = request('tableFilters.invoiceno.invoiceno');
                        $startDate = request('tableFilters.start_date.start_date');
                        $endDate = request('tableFilters.end_date.end_date');
                        $customerId = request('tableFilters.customer_id');
                        $productId = request('tableFilters.product_id.product_id');

                        // Apply the same filters as in the table
                        if ($invoiceNo) {
                            $query->where('invoiceno', $invoiceNo);
                        }

                        if ($startDate) {
                            $query->where('order_date', '>=', $startDate);
                        }

                        if ($endDate) {
                            $query->where('order_date', '<=', $endDate);
                        }

                        if ($customerId) {
                            $query->where('customer_id', $customerId);
                        }

                        if ($productId) {
                            $query->whereHas('orderitems', function ($query) use ($productId) {
                                $query->where('product_id', $productId);
                            });
                        }

                        // Include the same relationships and calculations as in the table query
                        $query->with([
                            'orderitems:id,product_id,order_id',
                            'orderitems.product:id,product_name,product_code',
                            'customer:id,customer_name',
                        ])->withSum('orderitems', 'purchase_cost');
                    }),
            ])
            ->filters([
                Filter::make('invoiceno')
                    ->label('')
                    ->form([
                        TextInput::make('invoiceno')
                            ->label('Bill No.')
                            ->autocomplete(false)
                            ->default(request('invoiceno'))
                            ->placeholder('Bill Number'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['invoiceno'],
                            fn ($query, $term) => $query->where('invoiceno', $term)
                        );
                    }),
                Filter::make('start_date')
                    ->label('')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->native(false)
                            ->placeholder('Start Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['start_date'],
                            fn ($query, $term) => $query->where('order_date', '>=', $term)
                        );
                    }),
                Filter::make('end_date')
                    ->label('')
                    ->form([
                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->native(false)
                            ->placeholder('End Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['end_date'],
                            fn ($query, $term) => $query->where('order_date', '<=', $term)
                        );
                    }),

                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->placeholder('Select Customer')
                    ->options(Customer::query()->where('is_default', '!=', 1)->pluck('customer_name', 'id'))
                    ->default(request('customer_id'))
                    ->searchable(),

                Filter::make('product_id')
                    ->label('')
                    ->form([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(Product::query()->pluck('product_name', 'id'))
                            ->placeholder('Select Product')
                            ->searchable(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['product_id'],
                            fn ($query, $term) => $query->whereHas('orderitems', function ($query) use ($term) {
                                $query->where('product_id', $term);
                            })
                        );
                    }),

            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->hiddenFilterIndicators()
            ->actions([
                ActionGroup::make([

                    Action::make('Invoice')
                        ->label('Invoice')
                        ->icon('heroicon-s-printer')
                        ->url(fn (Order $record) => route('filament.admin.resources.sales.pos-receipt', ['record' => $record->id])),

                    Action::make('Show')
                        ->label('Show')
                        ->icon('heroicon-s-computer-desktop')
                        ->url(fn (Order $record) => route('filament.admin.resources.sales.pos-show', ['record' => $record->id])),

                    Action::make('return_order')
                        ->label('Return')
                        ->icon('fas-rotate-left')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            try {
                                DB::beginTransaction();

                                $returnList = ReturnList::where('order_id', $record->id)->first();
                                if ($returnList) {
                                    Notification::make()
                                        ->danger()
                                        ->title('Return List Already Exists')
                                        ->send();

                                    return;
                                }
                                $orderItems = OrderItem::where('order_id', $record->id)->where('total_qty', '>', 0)->get();
                                if ($orderItems->count() == 0) {
                                    Notification::make()
                                        ->danger()
                                        ->title('This order has no products.')
                                        ->send();

                                    return;
                                }

                                // ///////////////

                                foreach ($orderItems as $orderitem) {

                                    $product = Product::find($orderitem->product_id);
                                    $product->productdetails()->increment('available_stock', $orderitem->total_qty);
                                    $product->productdetails()->increment('returned', $orderitem->total_qty);
                                    // $product->productdetails()->decrement('sold', $orderitem->total_qty);

                                    $productDetails = $product->productdetails;
                                    // Use a single update to modify multiple columns
                                    $productDetails->update([
                                        // 'sold_in_text' => getTotalStockInText($orderitem->product_id, $productDetails->sold),
                                        'available_stock_in_text' => getTotalStockInText($orderitem->product_id, $productDetails->available_stock),
                                        'returned_in_text' => getTotalStockInText($orderitem->product_id, $productDetails->returned),
                                    ]);

                                    foreach ($orderitem->purchase_ids ?? [] as $purchase_id) {
                                        $purchaseItem = PurchaseItem::find($purchase_id['purchase_item_id']);

                                        $purchaseItem->increment('available_qty', $purchase_id['qty']);

                                        $purchaseItem->update([
                                            'available_purchase_value' => singleUnitPurchasePrice($purchaseItem->product_id, $purchaseItem->rate ?: 0) * $purchaseItem->available_qty,
                                        ]);
                                    }
                                }

                                // ///////////////

                                $returnList = ReturnList::create([
                                    'order_id' => $record->id,
                                    'customer_id' => $record->customer_id,
                                    'invoiceno' => $record->invoiceno,
                                    'sell_date' => $record->order_date,
                                    'discount' => ($record->total_no_discount ?: 0) - ($record->receivable ?: 0),
                                    'receivable' => $record->receivable,
                                    'total_no_discount' => $record->total_no_discount,
                                    'profit' => $record->profit,
                                    'paid' => $record->paid,
                                    'due' => $record->due,
                                ]);

                                foreach ($orderItems as $item) {
                                    ReturnListProduct::create([
                                        'return_list_id' => $returnList->id,
                                        'product_id' => $item->product_id,
                                        'total_in_text' => $item->total_in_text,
                                        'total_qty' => $item->total_qty,
                                        'purchase_cost' => $item->purchase_cost,
                                        'over_sale_qty' => $item->over_sale_qty,
                                    ]);
                                    $item->decrement('total_qty', $item->total_qty);
                                    $item->decrement('purchase_cost', $item->purchase_cost);
                                    $item->decrement('over_sale_qty', $item->over_sale_qty);
                                }

                                $record->increment('product_returned', $record->receivable);
                                $record->decrement('receivable', $record->receivable);
                                $record->decrement('due', $record->due);

                                // $record->decrement('paid', $record->paid);

                                $record->decrement('total_no_discount', $record->total_no_discount);
                                $record->decrement('profit', $record->profit);
                                DB::commit();
                            } catch (\Throwable $th) {
                                // throw $th;
                                DB::rollBack();
                                throw $th;
                            }
                            Notification::make()
                                ->success()
                                ->title('Return List Created Successfully')
                                ->send();
                        }),

                    Action::make('add_payment')
                        ->label('Add Payment')
                        ->icon('fas-money-bill-wave')
                        ->form([
                            DatePicker::make('date')
                                ->label('Payment Date')
                                ->native(false)
                                ->default(now())
                                ->required(),
                            Select::make('account')
                                ->label('Transaction Account')
                                ->searchable()
                                ->options(Account::query()->pluck('name', 'id'))
                                ->rules([
                                    'required',
                                    Rule::exists('accounts', 'id'),
                                ])
                                ->required(),
                            TextInput::make('amount')
                                ->label('Amount')
                                ->numeric()
                                ->rules([
                                    'required',
                                    'numeric',
                                    'min:0',
                                    'max:9999999999',
                                ])
                                ->default(fn (Order $record) => $record->due)
                                ->required(),
                            Textarea::make('note')
                                ->label('Note'),

                        ])
                        ->action(function (array $data, Order $record) {
                            $order = Order::query()->findOrFail($record->id);

                            if ($order->due < $data['amount']) {
                                Notification::make()
                                    ->danger()
                                    ->title('Amount is greater than due amount')
                                    ->send();

                                return;
                            } else {
                                try {
                                    DB::beginTransaction();

                                    $order->increment('paid', $data['amount']);
                                    $order->decrement('due', $data['amount']);

                                    $payment = Payment::create([
                                        'customer_id' => $order->customer_id,
                                        'payment_date' => $data['date'],
                                        'payment_type' => 'Cash Received',
                                        'note' => $data['note'],
                                    ]);

                                    $order->histories()->create([
                                        'amount' => $data['amount'],
                                        'account_id' => $data['account'],
                                        'date' => today(),
                                        'type' => HistoryTypeEnum::RECEIVED->value,
                                        'customer_id' => $order->customer_id,
                                        'payment_id' => $payment->id,
                                    ]);

                                    Account::find($data['account'])->increment('current_balance', $data['amount']);
                                    DB::commit();
                                } catch (\Throwable $th) {
                                    DB::rollBack();
                                }
                                Notification::make()->success()
                                    ->title('Payment Added Successfully')
                                    ->send();
                            }
                        })
                        ->modalHeading('Add Payment')
                        ->modalButton('Add Payment')
                        ->modalCancelAction(false)
                        ->modalWidth('sm'),
                    Action::make('delete')
                        ->label('Delete')
                        ->color('danger')
                        ->icon('heroicon-s-trash')
                        ->requiresConfirmation()
                        ->action(function (Order $record) {

                            try {
                                DB::beginTransaction();

                                $order = $record->load('histories', 'orderitems', 'returnlist.returnlistproducts');

                                $histories = $order->histories;

                                foreach ($histories as $history) {
                                    Account::query()->where('id', $history->account_id)->decrement('current_balance', $history->amount);
                                    $history->delete();
                                }

                                $orderitems = $order->orderitems;
                                $returnlistproducts = $order->returnlist->returnlistproducts;
                                $countReturnlistproducts = count($returnlistproducts);

                                foreach ($orderitems as $orderitem) {

                                    $product = Product::find($orderitem->product_id);

                                    $returnListProduct = collect($returnlistproducts)->where('product_id', $product->id)->first();

                                    $product->productdetails()->increment('available_stock', $orderitem->total_qty);

                                    $product->productdetails()->decrement('sold', ($orderitem->total_qty + ($returnListProduct?->total_qty ?? 0)));

                                    $productDetails = $product->productdetails;
                                    // Use a single update to modify multiple columns
                                    $productDetails->update([
                                        'sold_in_text' => getTotalStockInText($orderitem->product_id, $productDetails->sold),
                                        'available_stock_in_text' => getTotalStockInText($orderitem->product_id, $productDetails->available_stock),
                                    ]);

                                    // dd($orderitem->purchase_ids);
                                    if ($countReturnlistproducts == 0) {
                                        // dd($orderitem->purchase_ids);
                                        foreach ($orderitem->purchase_ids ?? [] as $purchase_id) {
                                            // dd($purchase_id);
                                            $purchaseItem = PurchaseItem::find($purchase_id['purchase_item_id']);

                                            $purchaseItem->increment('available_qty', $purchase_id['qty']);

                                            $purchaseItem->update([
                                                'available_purchase_value' => singleUnitPurchasePrice($purchaseItem->product_id, $purchaseItem->rate ?: 0) * $purchaseItem->available_qty,
                                            ]);
                                        }
                                    }
                                    $orderitem->delete();
                                }

                                $order->delete();

                                DB::commit();
                            } catch (\Throwable $th) {

                                DB::rollBack();
                                throw $th;
                            }

                            Notification::make()->success()
                                ->title('Order Deleted Successfully')
                                ->send();

                            // return redirect()->route('filament.pages.sales');

                        }),
                ])->dropdown(true)
                    ->label('Actions')
                    ->button()
                    ->size('sm')
                    ->icon('fas-gears'),
            ])
            ->bulkActions([])
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'pos-receipt' => Pages\PosReceipt::route('/pos-receipt/{record}'),
            'pos-show' => Pages\SalesShow::route('/pos-show/{record}'),
        ];
    }
}
