<?php

namespace App\Filament\Exports;

use App\Models\PurchaseItem;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PurcahseReportExporter extends Exporter
{
    protected static ?string $model = PurchaseItem::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('purchase.purchase_date')
                ->label('Date'),
            ExportColumn::make('purchase.billno')
                ->label('Purchase No'),
            ExportColumn::make('product.product_name')
                ->label('Product Name'),
            ExportColumn::make('total_in_text')
                ->label('Quantity'),
            ExportColumn::make('rate')
                ->label('Unit Price'),
            ExportColumn::make('total_rate')
                ->label('Subtotal'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your purcahse report export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
