<?php

namespace App\Filament\Pages;

use App\Models\Product;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class Stock extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'fas-cubes-stacked';

    protected static string $view = 'filament.pages.stock';

    protected static ?string $navigationGroup = 'Sale & Purchase';

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->with(['category', 'productdetails'])->latest())
            ->columns([
                ImageColumn::make('product_image')
                    ->label('Image')
                    ->defaultImageUrl('/images/notfound.jpg'),
                TextColumn::make('product_name')
                    ->extraAttributes(['class' => 'max-w-[250px] whitespace-normal'])
                    ->label('Product')
                    ->getStateUsing(fn($record) => $record->product_name . ' - ' . $record->product_code . ''),
                TextColumn::make('category.name')
                    ->label('Category'),

                TextColumn::make('sale_price')
                    ->label('Price')
                    ->getStateUsing(fn($record) => number_format($record->sale_price, 2, '.', '')),
                TextColumn::make('purchase_cost')
                    ->label('Cost')
                    ->getStateUsing(fn($record) => number_format($record->purchase_cost, 2, '.', '')),
                TextColumn::make('productdetails.purchased_in_text')
                    ->label('Purchased'),

                TextColumn::make('productdetails.sold_in_text')
                    ->label('Sold'),
                TextColumn::make('productdetails.damaged_in_text')
                    ->label('Damaged'),
                TextColumn::make('productdetails.returned_in_text')
                    ->label('Returned'),
                TextColumn::make('productdetails.available_stock_in_text')
                    ->label('Available Stock'),
                TextColumn::make('sale_value')
                    ->getStateUsing(function ($record) {
                        $val = ($record->productdetails?->single_unit_sale_price ?: 0) * ($record->productdetails?->available_stock ?: 0);

                        return number_format($val, 2, '.', '') . ' ' . 'Tk';
                    }),
                TextColumn::make('purchase_value')
                    ->getStateUsing(function ($record) {
                        $val = ($record->productdetails?->single_unit_purchase_price ?: 0) * ($record->productdetails?->available_stock ?: 0);

                        return number_format($val, 2, '.', '') . ' ' . 'Tk';
                    }),

            ])
            ->filters([

                SelectFilter::make('id')
                    ->label('Select a Product')
                    ->options(Product::query()->pluck('product_name', 'id'))
                    ->placeholder('Select a Product')
                    ->searchable(),

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
                            fn($query, $term) => $query->where('product_code', $term)
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
                            fn($query, $term) => $query->where('product_name', 'like', '%' . $term . '%')
                        );
                    }),

                SelectFilter::make('category_id')
                    ->label('Select a Category')
                    ->relationship('category', 'name')
                    ->placeholder('Select a Category')
                    ->preload()
                    ->searchable(),

                SelectFilter::make('brand_id')
                    ->label('Select a Brand')
                    ->relationship('Brand', 'brand_name')
                    ->placeholder('Select a Brand')
                    ->preload()
                    ->searchable(),

            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->hiddenFilterIndicators()
            ->paginated([10, 25, 50, 100]);
    }
}
