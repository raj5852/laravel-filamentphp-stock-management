<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class StockExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            // ExportColumn::make('product_code')
            //     ->label('Product Code'),

            ExportColumn::make('product_name')
                ->label('Product Name'),

            ExportColumn::make('category.name')
                ->label('Category'),

            // ExportColumn::make('brand.brand_name')
            //     ->label('Brand'),

            ExportColumn::make('sale_price')
                ->label('Sale Price')
                ->formatStateUsing(fn($state) => number_format($state, 2, '.', '') . ' Tk'),

            ExportColumn::make('purchase_cost')
                ->label('Purchase Cost')
                ->formatStateUsing(fn($state) => number_format($state, 2, '.', '') . ' Tk'),

            ExportColumn::make('productdetails.purchased_in_text')
                ->label('Purchased'),

            ExportColumn::make('productdetails.sold_in_text')
                ->label('Sold'),

            ExportColumn::make('productdetails.damaged_in_text')
                ->label('Damaged'),

            ExportColumn::make('productdetails.returned_in_text')
                ->label('Returned'),

            ExportColumn::make('productdetails.available_stock_in_text')
                ->label('Available Stock'),

            ExportColumn::make('sale_value')
                ->label('Sale Value')
                ->getStateUsing(function (Product $record) {
                    $val = ($record->productdetails?->single_unit_sale_price ?: 0) * ($record->productdetails?->available_stock ?: 0);
                    return number_format($val, 2, '.', '') . ' Tk';
                }),

            ExportColumn::make('purchase_value')
                ->label('Purchase Value')
                ->getStateUsing(function (Product $record) {
                    $val = ($record->productdetails?->single_unit_purchase_price ?: 0) * ($record->productdetails?->available_stock ?: 0);
                    return number_format($val, 2, '.', '') . ' Tk';
                }),

        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your stock export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
