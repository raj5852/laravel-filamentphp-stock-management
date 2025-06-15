<?php

namespace App\Filament\Exports;

use App\Models\History;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PayToSupplierExporter extends Exporter
{
    protected static ?string $model = History::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('supplier.supplier_name')
                ->label('Supplier Name'),
            ExportColumn::make('payment.payment_date')
                ->label('Payment Date'),
            ExportColumn::make('amount')
                ->label('Amount'),
        ];
    }

    public static function modalHeading(): string
    {
        return 'Export Pay to Supplier';
    }

    // public static function getCompletedNotificationTitle(Export $export): string
    // {
    //     return 'Pay to Supplier Export Completed';
    // }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your pay to supplier export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
