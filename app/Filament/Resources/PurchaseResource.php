<?php

namespace App\Filament\Resources;

use App\Filament\Exports\PurchaseExporter;
use App\Filament\Resources\PurchaseResource\Pages;
use App\HistoryTypeEnum;
use App\Models\Account;
use App\Models\Damage;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'fas-gifts';

    protected static ?string $navigationGroup = 'Sale & Purchase';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Purchase::query()->with([
                'supplier',
                'purchaseItems:id,product_id,purchase_id',
                'purchaseItems.product:id,product_name,product_code',
            ])
                ->latest())
            ->columns([
                Tables\Columns\TextColumn::make('billno')->label('Bill No.'),
                Tables\Columns\TextColumn::make('supplier.supplier_name'),
                Tables\Columns\TextColumn::make('purchase_date')->date(),
                Tables\Columns\TextColumn::make('purchaseitems')->label('Items')
                    ->formatStateUsing(function ($record) {
                        // Fetch related purchaseItems with product details
                        $items = $record->purchaseItems->map(function ($purchaseItem) {
                            $productName = $purchaseItem->product->product_name ?? 'N/A';
                            $productCode = $purchaseItem->product->product_code ?? 'N/A';

                            return "{$productName} | {$productCode}"; // Format: "Product Name (Product Code)"
                        })->toArray();

                        // Format as a list (ul > li)
                        return '<ul class="list-disc pl-5 space-y-2">'.implode('', array_map(fn ($item) => "<li class='max-w-[300px] whitespace-normal '>{$item}</li>", $items)).'</ul>';
                    })
                    ->html(),

                Tables\Columns\TextColumn::make('payable')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, '.', '').' Tk')
                    ->summarize(
                        Sum::make()->formatStateUsing(fn ($state) => number_format($state, 2, '.', '').' Tk')->label('Total payable')
                    ),
                Tables\Columns\TextColumn::make('paid')->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', '').' Tk')
                    ->summarize(
                        Sum::make()->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', '').' Tk')->label('Total Paid')
                    ),
                Tables\Columns\TextColumn::make('due')->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', '').' Tk')
                    ->summarize(
                        Sum::make()->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', '').' Tk')->label('Total Due')
                    ),

            ])
            ->headerActions([
                Tables\Actions\Action::make('Purchase')
                    ->label('Add Purchase')
                    ->icon('heroicon-o-plus')
                    ->url(fn (): string => route('filament.admin.resources.purchases.add-purchase')),
                ExportAction::make()
                    ->exporter(PurchaseExporter::class)
                    ->label('Export')
                    ->columnMapping(false)
                    ->icon('fas-download')
                    ->modifyQueryUsing(function (Builder $query) {
                        // Get the current filter values from the request
                        $billNo = request('tableFilters.billno.billno');
                        $startDate = request('tableFilters.start_date.start_date');
                        $endDate = request('tableFilters.end_date.end_date');
                        $supplierId = request('tableFilters.supplier_id');
                        $productId = request('tableFilters.product_id.product_id');

                        // Apply the same filters as in the table
                        if ($billNo) {
                            $query->where('billno', $billNo);
                        }

                        if ($startDate) {
                            $query->where('purchase_date', '>=', $startDate);
                        }

                        if ($endDate) {
                            $query->where('purchase_date', '<=', $endDate);
                        }

                        if ($supplierId) {
                            $query->where('supplier_id', $supplierId);
                        }

                        if ($productId) {
                            $query->whereHas('purchaseItems', function ($query) use ($productId) {
                                $query->where('product_id', $productId);
                            });
                        }

                        // Include the same relationships as in the table query
                        $query->with([
                            'supplier',
                            'purchaseItems:id,product_id,purchase_id',
                            'purchaseItems.product:id,product_name,product_code',
                        ]);
                    }),

            ])
            ->filters([

                Filter::make('billno')
                    ->label('')
                    ->form([
                        TextInput::make('billno')
                            ->label('Bill No')
                            ->default(request('billno'))
                            ->autocomplete(false)
                            ->placeholder('Bill Number'),

                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['billno'],
                            fn ($query, $term) => $query->where('billno', $term)
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
                            fn ($query, $term) => $query->where('purchase_date', '>=', $term)
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
                            fn ($query, $term) => $query->where('purchase_date', '<=', $term)
                        );
                    }),

                SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->placeholder('Select Supplier')
                    ->options(Supplier::query()->where('is_default', '!=', 1)->pluck('supplier_name', 'id'))
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
                            fn ($query, $term) => $query->whereHas('purchaseItems', function ($query) use ($term) {
                                $query->where('product_id', $term);
                            })
                        );
                    }),

            ], layout: FiltersLayout::AboveContent)
            ->actions([

                ActionGroup::make([
                    Action::make('Invoice')
                        ->label('Invoice')
                        ->icon('heroicon-s-printer')
                        ->url(fn (Purchase $record) => route('filament.admin.resources.purchases.purchase-invoice', ['record' => $record->id])),
                    Action::make('Show')
                        ->label('Show')
                        ->icon('heroicon-s-computer-desktop')
                        ->url(fn (Purchase $record) => route('filament.admin.resources.purchases.purchase-show', ['record' => $record->id])),

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
                                ->default(fn (Purchase $record) => $record->due)
                                ->required(),
                            Textarea::make('note')
                                ->label('Note'),

                        ])
                        ->action(function (array $data, Purchase $record) {

                            $purchase = Purchase::query()->findOrFail($record->id);

                            if ($data['amount'] > $purchase->due) {
                                Notification::make()->warning()
                                    ->title('Payment amount is greater than due')
                                    ->send();

                                return;
                            } else {
                                try {
                                    DB::beginTransaction();
                                    $purchase->increment('paid', $data['amount']);
                                    $purchase->decrement('due', $data['amount']);

                                    $payment = Payment::create([
                                        'supplier_id' => $record->supplier_id,
                                        'payment_date' => $data['date'],
                                        'payment_type' => 'Cash Pay',
                                        'note' => $data['note'],
                                    ]);

                                    $purchase->histories()->create([
                                        'amount' => $data['amount'],
                                        'account_id' => $data['account'],
                                        'date' => today(),
                                        'type' => HistoryTypeEnum::SPENT_OR_WITHDRAW->value,
                                        'supplier_id' => $record->supplier_id,
                                        'payment_id' => $payment->id,
                                    ]);

                                    Account::find($data['account'])->decrement('current_balance', $data['amount']);

                                    Notification::make()->success()
                                        ->title('Payment Added Successfully')
                                        ->send();
                                    DB::commit();
                                } catch (\Throwable $th) {
                                    DB::rollBack();
                                }
                            }
                        })
                        ->modalHeading('Add Payment')
                        ->modalButton('Add Payment')
                        ->modalCancelAction(false)
                        ->modalWidth('sm'),
                    DeleteAction::make()
                        ->before(function (Purchase $record, $action) {
                            $damage = Damage::query()
                            ->whereJsonContains('purchase_ids', ['purchase_id' => strval($record->id)])
                            ->orWhereJsonContains('purchase_ids', ['purchase_id' => (int)$record->id])
                            ->exists();

                            $sales = OrderItem::query()
                                ->whereJsonContains('purchase_ids', ['purchase_id' => strval($record->id)])
                                ->orWhereJsonContains('purchase_ids', ['purchase_id' => (int)$record->id])
                                ->exists();

                            if ($damage || $sales) {
                                Notification::make()->danger()->title('You can\'t delete it.')->send();
                                $action->cancel();
                            }

                            foreach ($record->histories as $item) {
                                $item->account->increment('current_balance', $item->amount);
                                $item->delete();
                            }

                            $products = [];
                            foreach ($record->purchaseitems as $item) {
                                $product = Product::with('productdetails')->find($item->product_id);
                                if ($product) {
                                    $productDetails = $product->productdetails;

                                    // Avoid multiple find queries for the same product
                                    if (! isset($products[$item->product_id])) {
                                        $products[$item->product_id] = $product;
                                    }

                                    // Update product details
                                    $productDetails->decrement('available_stock', $item->total_qty);
                                    $productDetails->decrement('purchased', $item->total_qty);

                                    // Use a single update to modify multiple columns
                                    $productDetails->update([
                                        'purchased_in_text' => getTotalStockInText($item->product_id, $productDetails->purchased),
                                        'available_stock_in_text' => getTotalStockInText($item->product_id, $productDetails->available_stock),
                                    ]);
                                }
                                $item->delete();
                            }

                            $record->delete();
                            Notification::make()->success()->title('Deleted Successfully')->send();
                        }),
                ])->dropdown(true)

                    ->label('Actions')
                    ->button()
                    ->size('sm')
                    ->hidden(function ($record) {
                        // dd($record);
                        return $record->is_purchase == 0;
                    })
                    ->icon('fas-gears'),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->filtersFormColumns(4)
            ->hiddenFilterIndicators()
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
            'index' => Pages\ListPurchases::route('/'),
            'add-purchase' => Pages\CreateNewPurchase::route('/add-purchase'),
            'purchase-invoice' => Pages\PurchaseInvoice::route('/invoice/{record}'),
            'purchase-show' => Pages\PurchaseShow::route('/purchase-show/{record}'),
        ];
    }
}
