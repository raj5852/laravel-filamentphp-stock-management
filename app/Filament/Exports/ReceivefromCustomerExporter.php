<?php

namespace App\Filament\Exports;

use App\Models\History;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ReceivefromCustomerExporter extends Exporter
{
    protected static ?string $model = History::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('customer.customer_name')
                ->label('Customer Name'),
            ExportColumn::make('payment.payment_date')
                ->label('Payment Date'),
            ExportColumn::make('amount')
                ->label('Amount'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your receivefrom customer export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
