<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product as ModelsProduct;
use App\Models\PurchaseItem;
use App\Models\ReturnList as ModelsReturnList;
use App\Models\ReturnListProduct;
use App\Models\Shop\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class ReturnList extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'fas-right-left';

    protected static ?string $navigationGroup = 'Sale & Purchase';

    protected static string $view = 'filament.pages.return-list';

    public static function canAccess(): bool
    {
        return auth()->user()->can('return list');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ModelsReturnList::query()->latest())
            ->columns([
                TextColumn::make('invoiceno')->label('Bill No.')
                    ->getStateUsing(function ($record) {
                        // return 'Invoice#' . $record->invoiceno;
                        return new HtmlString("<div><a href='/user/sales/pos-receipt/{$record->order_id}' target='_blank' class='text-indigo-500 hover:underline'>Invoice#{$record->invoiceno}</a></div>");
                    }),
                TextColumn::make('customer.customer_name')->label('Customer'),

                TextColumn::make('returnListProducts')->label('Items')
                    ->formatStateUsing(function ($record) {

                        $items = $record->returnListProducts->map(function ($purchaseItem) {

                            $productName = $purchaseItem->product->product_name ?? 'N/A';

                            return "{$productName} * {$purchaseItem->total_in_text}"; // Format: "Product Name (Product Code)"
                        })->toArray();

                        // Format as a list (ul > li)
                        return '<ul class="list-disc pl-5 space-y-2">'.implode('', array_map(fn ($item) => "<li class='max-w-[300px] whitespace-normal'>{$item}</li>", $items)).'</ul>';
                    })
                    ->html(),

                TextColumn::make('sell_date')->date(),
                TextColumn::make('discount')->getStateUsing(function ($record) {
                    return number_format($record->discount, 2).' Tk';
                }),
                TextColumn::make('receivable')
                    ->label('Total')
                    ->getStateUsing(function ($record) {
                        return number_format($record->receivable, 2).' Tk';
                    }),
            ])
            ->filters([
                Filter::make('start_date')
                    ->label('')
                    ->form([
                        DatePicker::make('start_date')
                            ->label(' ')
                            ->native(false)
                            ->placeholder('Start Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['start_date'],
                            fn ($query, $term) => $query->where('sell_date', '>=', $term)
                        );
                    }),
                Filter::make('end_date')
                    ->label('')
                    ->form([
                        DatePicker::make('end_date')
                            ->label(' ')
                            ->native(false)
                            ->placeholder('End Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['end_date'],
                            fn ($query, $term) => $query->where('sell_date', '<=', $term)
                        );
                    }),
                Filter::make('invoiceno')
                    ->form([
                        TextInput::make('invoiceno')
                            ->label('')
                            ->autocomplete(false)
                            ->placeholder('Bill No.'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['invoiceno'],
                            fn ($query, $term) => $query->where('invoiceno', $term)
                        );
                    }),
                SelectFilter::make('customer_id')
                    ->label(' ')
                    ->placeholder('Select Customer')
                    ->options(Customer::query()->where('is_default', '!=', 1)->pluck('customer_name', 'id'))
                    ->default(request('customer_id'))
                    ->searchable(),

            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                ActionGroup::make([
                    Action::make('delete')
                        ->label('Delete')
                        ->color('danger')
                        ->icon('heroicon-s-trash')
                        ->requiresConfirmation()
                        ->action(function (ModelsReturnList $record) {

                            $isOrder = 0;

                            $orderItems = OrderItem::where('order_id', $record->order_id)->get();

                            foreach ($orderItems as $orderItem) {

                                foreach ($orderItem->purchase_ids as $purchaseid) {

                                    $purchaseItem = PurchaseItem::find($purchaseid['purchase_item_id']);

                                    if ($purchaseItem->available_qty < $purchaseid['qty']) {
                                        $isOrder += 1;
                                    }
                                }
                            }
                            if ($isOrder > 0) {
                                Notification::make()
                                    ->title('You can not delete this return list')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            try {
                                DB::beginTransaction();
                                foreach ($orderItems as $orderitem) {

                                    $product = ModelsProduct::find($orderitem->product_id);

                                    $returnListProduct = ReturnListProduct::where('product_id', $product->id)->first();

                                    $product->productdetails()->decrement('available_stock', $returnListProduct->total_qty);
                                    $product->productdetails()->decrement('returned', $returnListProduct->total_qty);
                                    // $product->productdetails()->increment('sold', $returnListProduct->total_qty);

                                    $productDetails = $product->productdetails;
                                    // Use a single update to modify multiple columns
                                    $productDetails->update([
                                        // 'sold_in_text' => getTotalStockInText($orderitem->product_id, $productDetails->sold),
                                        'available_stock_in_text' => getTotalStockInText($orderitem->product_id, $productDetails->available_stock),
                                        'returned_in_text' => getTotalStockInText($orderitem->product_id, $productDetails->returned),
                                    ]);

                                    foreach ($orderitem->purchase_ids ?? [] as $purchase_id) {
                                        $purchaseItem = PurchaseItem::find($purchase_id['purchase_item_id']);

                                        $purchaseItem->decrement('available_qty', $purchase_id['qty']);

                                        $purchaseItem->update([
                                            'available_purchase_value' => singleUnitPurchasePrice($purchaseItem->product_id, $purchaseItem->rate ?: 0) * $purchaseItem->available_qty,
                                        ]);
                                    }

                                    $orderitem->increment('total_qty', $returnListProduct->total_qty);
                                    $orderitem->increment('purchase_cost', $returnListProduct->purchase_cost);
                                    $orderitem->increment('over_sale_qty', $returnListProduct->over_sale_qty);

                                    if ($orderitem->varient_uniqid != '') {
                                        updateProductVarient($orderitem->product_id, $orderitem->varient_uniqid, available_stock: -$orderitem->total_qty);
                                    }
                                }

                                $order = Order::find($record->order_id);

                                $order->decrement('product_returned', $record->receivable);
                                $order->increment('receivable', $record->receivable);
                                $order->increment('due', $record->due);

                                $order->increment('total_no_discount', $record->total_no_discount);
                                $order->increment('profit', $record->profit);

                                // ////

                                $record->delete();

                                DB::commit();
                            } catch (\Throwable $th) {
                                DB::rollBack();
                                throw $th;
                            }
                            Notification::make()->success()
                                ->title('Return product Deleted Successfully')
                                ->send();
                        }),
                ])->dropdown(true)
                    ->label('Actions')
                    ->button()
                    ->size('sm')
                    ->icon('fas-gears'),
            ])
            ->bulkActions([
                // ...
            ])
            ->paginated([10, 25, 50, 100]);
    }
}
