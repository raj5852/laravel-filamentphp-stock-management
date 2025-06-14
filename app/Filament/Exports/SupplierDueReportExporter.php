<?php

namespace App\Filament\Exports;

use App\Models\Supplier;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class SupplierDueReportExporter extends Exporter
{
    protected static ?string $model = Supplier::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('supplier_name')
                ->label('Supplier Name'),
            ExportColumn::make('email')
                ->label('Email'),
            ExportColumn::make('phone')
                ->label('Phone'),
            ExportColumn::make('address')
                ->label('Address'),
            ExportColumn::make('invoice_due')
                ->getStateUsing(function (Supplier $record) {
                    return number_format($record->purchases_sum_due ?? 0, 2).' TK';
                })
                ->label('Invoice Due'),
            ExportColumn::make('direct_due')
                ->label('Direct Due')
                ->getStateUsing(function (Supplier $record) {
                    return number_format(abs($record->wallet ?? 0), 2).' TK';
                }),
            ExportColumn::make('total_due')
                ->label('Total Due')
                ->getStateUsing(function (Supplier $record) {
                    return number_format((abs($record->purchases_sum_due ?? 0) + abs($record->wallet ?? 0)), 2).' TK';
                }),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your supplier due report export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
