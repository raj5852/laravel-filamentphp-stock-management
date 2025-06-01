<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class LowStockReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $low_stock_quantity;

    protected static ?string $navigationIcon = 'fas-triangle-exclamation';

    protected static string $view = 'filament.pages.low-stock-report';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 8;

    public function mount()
    {
        $setting = Setting::first();
        $this->low_stock_quantity = $setting?->low_stock_quantity ?? 0;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->whereRelation('productdetails', 'available_stock', '>=', $this->low_stock_quantity)->with('productdetails', 'category:id,name'))
            ->columns([

                ImageColumn::make('product_image')->label('Image')->defaultImageUrl('/images/notfound.jpg'),
                TextColumn::make('product_name')->label('Product Name'),
                TextColumn::make('category.name')->label('Category'),
                TextColumn::make('sale_price')->label('Price'),
                TextColumn::make('productdetails.sold_in_text')->label('Sale'),
                TextColumn::make('productdetails.purchased_in_text')->label('Purchases'),
                TextColumn::make('productdetails.available_stock_in_text')->label('Available Stock'),
                TextColumn::make('productdetails.single_unit_sale_price')
                    ->getStateUsing(function ($record) {
                        return number_format($record->productdetails?->single_unit_sale_price * $record->productdetails?->available_stock, 2, '.', '').' TK';
                    })
                    ->label('Sell Value'),

            ])
            ->filters(
                [
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
                                fn ($query, $term) => $query->where('id', $term)
                            );
                        }),

                    Filter::make('product_code')
                        ->label('')
                        ->form([
                            TextInput::make('product_code')
                                ->label('Product Code')
                                ->autocomplete(false)
                                ->placeholder('Product Code'),
                        ])
                        ->query(function ($query, array $data) {
                            return $query->when(
                                $data['product_code'],
                                fn ($query, $term) => $query->where('product_code', $term)
                            );
                        }),
                    Filter::make('product_name')
                        ->label('')
                        ->form([
                            TextInput::make('product_name')
                                ->label('Product Name')
                                ->autocomplete(false)
                                ->placeholder('Product Name'),
                        ])
                        ->query(function ($query, array $data) {
                            return $query->when(
                                $data['product_name'],
                                fn ($query, $term) => $query->where('product_name', 'like', "%{$term}%")
                            );
                        }),

                    Filter::make('category_id')
                        ->label('')
                        ->form([
                            Select::make('category_id')
                                ->label('Product')
                                ->options(Category::query()->pluck('name', 'id'))
                                ->placeholder('Select Category')
                                ->searchable(),
                        ])
                        ->query(function ($query, array $data) {
                            return $query->when(
                                $data['category_id'],
                                fn ($query, $term) => $query->where('category_id', $term)
                            );
                        }),
                ],
                FiltersLayout::AboveContent
            )
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ])
            ->filtersFormColumns(4)

            ->paginated([10, 25, 50, 100]);
    }
}
