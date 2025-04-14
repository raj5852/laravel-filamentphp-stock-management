<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'fas-cart-shopping';

    protected static ?string $navigationGroup = 'Sale & Purchase';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\Action::make('Purchase')
                    ->label('Add Purchase')
                    ->icon('heroicon-o-plus')
                    ->url(fn (): string => route('filament.admin.resources.purchases.add-purchase')),
            ])
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
                        return '<ul class="list-disc list-inside">'.implode('', array_map(fn ($item) => "<li>{$item}</li>", $items)).'</ul>';
                    })
                    ->html(),

                Tables\Columns\TextColumn::make('payable')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', '')),
                Tables\Columns\TextColumn::make('paid')->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', '')),
                Tables\Columns\TextColumn::make('due')->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', '')),

            ])
            ->filters([

                Filter::make('billno')
                    ->label('')
                    ->form([
                        TextInput::make('billno')
                            ->label('Bill No')
                            ->autocomplete(false)
                            ->placeholder('Bill Number'),

                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['billno'], fn ($query, $term) => $query->where('billno', $term)
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
                        return $query->when($data['start_date'], fn ($query, $term) => $query->where('purchase_date', '>=', $term)
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
                        return $query->when($data['end_date'], fn ($query, $term) => $query->where('purchase_date', '<=', $term)
                        );
                    }),

                SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->placeholder('Select Supplier')
                    ->options(Supplier::query()->pluck('supplier_name', 'id'))
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
                        return $query->when($data['product_id'], fn ($query, $term) => $query->whereHas('purchaseItems', function ($query) use ($term) {
                            $query->where('product_id', $term);
                        })
                        );
                    }),

            ], layout: FiltersLayout::AboveContent)
            ->actions([
                // Tables\Actions\EditAction::make(),
                ActionGroup::make([
                    Action::make('Invoice')
                        ->label('Invoice')
                        ->icon('heroicon-s-printer')
                        ->url(fn (Purchase $record) => route('filament.admin.resources.purchases.purchase-invoice', ['record' => $record->id])),
                    Action::make('Show')
                        ->label('Show')
                        ->icon('heroicon-s-computer-desktop')
                        ->url(fn (Purchase $record) => route('filament.admin.resources.purchases.purchase-show', ['record' => $record->id])),

                ])->dropdown(true)
                    ->label('Actions')
                    ->button()
                    ->size('sm')
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
