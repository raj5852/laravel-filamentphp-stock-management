<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class Sales extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'fas-bag-shopping';

    protected static string $view = 'filament.pages.sales';

    protected static ?string $navigationGroup = 'Sale & Purchase';

    public function table(Table $table): Table
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
                // ...
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
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
