<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesResource\Pages;
use App\HistoryTypeEnum;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SalesResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'fas-bag-shopping';

    protected static ?string $navigationGroup = 'Sale & Purchase';

    protected static ?string $pluralModelLabel = 'Sales';

    public static function form(Form $form): Form
    {
        return $form

            ->schema([

            ]);
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
                    'orderitems:id,product_id,order_id',
                    'orderitems.product:id,product_name,product_code',
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
                            $productName = $purchaseItem->product->product_name ?? 'N/A';
                            $productCode = $purchaseItem->product->product_code ?? 'N/A';

                            return "{$productName} | {$productCode}"; // Format: "Product Name (Product Code)"
                        })->toArray();

                        // Format as a list (ul > li)
                        return '<ul class="list-disc list-inside">'.implode('', array_map(fn ($item) => "<li>{$item}</li>", $items)).'</ul>';
                    })
                    ->html(),
                TextColumn::make('order_date')->label('Date')->date(),
                TextColumn::make('receivable')->formatStateUsing(function ($state) {
                    return number_format($state ?: 0, 2).' TK';
                }),
                TextColumn::make('paid')->formatStateUsing(function ($state) {
                    return number_format($state ?: 0, 2).' TK';
                }),
                TextColumn::make('due')->formatStateUsing(function ($state) {
                    return number_format($state ?: 0, 2).' TK';
                }),
                TextColumn::make('orderitems_sum_purchase_cost')->label('Purchase Cost')->formatStateUsing(function ($state) {
                    return number_format($state ?: 0, 2).' TK';
                }),
                TextColumn::make('Profit')->default(function (Order $record) {
                    $profit = $record['receivable'] - $record['orderitems_sum_purchase_cost'];

                    return number_format($profit, 2).' Tk';
                }),
                TextColumn::make('Status')->default(function (Order $record) {
                    return $record['receivable'] == $record['paid'] ? 'Paid' : 'Unpaid';
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
                        return $query->when($data['invoiceno'], fn ($query, $term) => $query->where('invoiceno', $term)
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
                        return $query->when($data['start_date'], fn ($query, $term) => $query->where('order_date', '>=', $term)
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
                        return $query->when($data['end_date'], fn ($query, $term) => $query->where('order_date', '<=', $term)
                        );
                    }),

                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->placeholder('Select Customer')
                    ->options(Customer::query()->pluck('customer_name', 'id'))
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
                        return $query->when($data['product_id'], fn ($query, $term) => $query->whereHas('orderitems', function ($query) use ($term) {
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
                                    'required', Rule::exists('accounts', 'id'),
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

                            DB::transaction(function () use ($record, $data) {
                                $order = Order::query()->findOrFail($record->id);

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

                            });

                            Notification::make()->success()
                                ->title('Payment Added Successfully')
                                ->send();

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

                            $order = $record->load('histories', 'orderitems');

                            $histories = $order->histories;

                            foreach ($histories as $history) {
                                Account::query()->where('id', $history->account_id)->decrement('current_balance', $history->amount);
                                $history->delete();
                            }

                            $orderitems = $order->orderitems;

                            foreach ($orderitems as $orderitem) {

                                $product = Product::find($orderitem->product_id);
                                $product->productdetails()->increment('available_stock', $orderitem->total_qty);
                                $product->productdetails()->decrement('sold', $orderitem->total_qty);

                                $productDetails = $product->productdetails;
                                // Use a single update to modify multiple columns
                                $productDetails->update([
                                    'sold_in_text' => getTotalStockInText($orderitem->product_id, $productDetails->sold),
                                    'available_stock_in_text' => getTotalStockInText($orderitem->product_id, $productDetails->available_stock),
                                ]);

                                $orderitem->delete();
                            }

                            $order->delete();

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
            ->bulkActions([

            ])
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
