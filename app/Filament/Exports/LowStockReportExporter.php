<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class LowStockReportExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('product_name')
                ->label('Product Name'),

            ExportColumn::make('category.name')
                ->label('Category'),

            ExportColumn::make('sale_price')
                ->label('Price'),

            ExportColumn::make('productdetails.sold_in_text')
                ->label('Sale'),

            ExportColumn::make('productdetails.purchased_in_text')
                ->label('Purchases'),

            ExportColumn::make('productdetails.available_stock_in_text')
                ->label('Available Stock'),

            ExportColumn::make('sell_value')
                ->label('Sell Value')
                ->getStateUsing(function ($record) {
                    return number_format(
                        $record->productdetails?->single_unit_sale_price * $record->productdetails?->available_stock,
                        2,
                        '.',
                        ''
                    ).' TK';
                }),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your low stock report export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
