<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [

            ExportColumn::make('product_name')
                ->label('Product Name'),
            ExportColumn::make('product_code')
                ->label('Product Code'),
            ExportColumn::make('category.name')
                ->label('Category'),
            ExportColumn::make('brand.name')
                ->label('Brand'),

            ExportColumn::make('sale_price')
                ->label('Price'),
            ExportColumn::make('purchase_cost')
                ->label('Cost'),
            ExportColumn::make('product_details')
                ->label('Details'),

        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your product export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
