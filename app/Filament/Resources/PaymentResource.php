<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\HistoryTypeEnum;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Expenses & Payment';

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return 'Payment'; // Singular label
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tables\Columns\TextColumn::make('id'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\Action::make('payment')
                    ->label('Add Payment')
                    ->icon('heroicon-o-plus')
                    ->url(fn (): string => route('filament.admin.resources.payments.add-payment')),
            ])
            ->query(Payment::query()->whereHas('histories')->withSum('histories', 'amount')->with('customer', 'supplier')->latest('id'))
            ->columns([
                TextColumn::make('details')
                    ->getStateUsing(function ($record) {

                        if ($record->customer_id != '') {
                            return new HtmlString('
                            <table class="w-1/2 border-collapse border border-gray-200 dark:border-gray-700 text-sm font-sans">
                               <tr>
                                   <td class="px-4 py-2 font-bold text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Customer Name:</td>
                                   <td class="px-4 py-2 text-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">'.$record->customer->customer_name.'</td>
                               </tr>
                               <tr>
                                   <td class="px-4 py-2 font-bold text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Phone:</td>
                                   <td class="px-4 py-2 text-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">'.$record->customer->phone.'</td>
                               </tr>
                           </table>
                           ');
                        }

                        if ($record->supplier_id != '') {
                            return new HtmlString('
                            <table class="w-1/2 border-collapse border border-gray-200 dark:border-gray-700 text-sm font-sans">
                               <tr>
                                   <td class="px-4 py-2 font-bold text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Supplier Name:</td>
                                   <td class="px-4 py-2 text-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">'.$record->supplier->supplier_name.'</td>
                               </tr>
                               <tr>
                                   <td class="px-4 py-2 font-bold text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Phone:</td>
                                   <td class="px-4 py-2 text-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">'.$record->supplier->phone.'</td>
                               </tr>
                           </table>
                           ');
                        }

                    }),

                TextColumn::make('payment_date')->date(),
                TextColumn::make('histories_sum_amount')
                    ->label('Amount')
                    ->getStateUsing(function ($record) {
                        return number_format($record->histories_sum_amount, 2, '.', '');
                    }),
                TextColumn::make('payment_type')->label('Payment Type'),
                TextColumn::make('note'),

            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->options(Customer::query()->where('is_default', '!=', 1)->pluck('customer_name', 'id'))
                    ->searchable(),

                SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->options(Supplier::query()->pluck('supplier_name', 'id'))
                    ->searchable(),

                Filter::make('start_date')
                    ->label('')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->native(false)
                            ->placeholder('Start Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['start_date'], fn ($query, $term) => $query->where('payment_date', '>=', $term)
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
                        return $query->when($data['end_date'], fn ($query, $term) => $query->where('payment_date', '<=', $term)
                        );
                    }),

            ], layout: FiltersLayout::AboveContent)

            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->before(function ($record, $action) {

                        $histories = $record->histories;

                        foreach ($histories as $history) {
                            $account = $history->account;

                            if ($history->type == HistoryTypeEnum::RECEIVED) {
                                $account->decrement('current_balance', $history->amount);
                            } elseif ($history->type == HistoryTypeEnum::SPENT_OR_WITHDRAW) {
                                $account->increment('current_balance', $history->amount);
                            }

                            if ($history->order_id != '') {
                                $order = $history->order;
                                $order->decrement('paid', $history->amount);
                                $order->increment('due', $history->amount);
                            }

                            if ($history->purchase_id != '') {
                                $purchase = $history->purchase;
                                $purchase->decrement('paid', $history->amount);
                                $purchase->increment('due', $history->amount);

                            }

                            if ($history->is_wallet_transaction === 1) {

                                if ($history->customer_id != '') {

                                    $customer = $history->customer;
                                    if ($history->type == HistoryTypeEnum::RECEIVED) {
                                        $customer->decrement('wallet', $history->amount);
                                    } elseif ($history->type == HistoryTypeEnum::SPENT_OR_WITHDRAW) {
                                        $customer->increment('wallet', $history->amount);
                                    }
                                }

                                if ($history->supplier_id != '') {
                                    $supplier = $history->supplier;
                                    if ($history->type == HistoryTypeEnum::RECEIVED) {
                                        $supplier->increment('wallet', $history->amount);
                                    } elseif ($history->type == HistoryTypeEnum::SPENT_OR_WITHDRAW) {
                                        $supplier->decrement('wallet', $history->amount);
                                    }
                                }
                            }

                            $history->delete();
                        }

                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->filtersFormColumns(4)
            // ->hiddenFilterIndicators()
            ->paginated([10, 20, 50, 100]);
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'add-payment' => Pages\AddPayment::route('/add-payment'),
            // 'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
