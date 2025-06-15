<?php

namespace App\Filament\Exports;

use App\Models\Customer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CustomerDueReportExporter extends Exporter
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('customer_name')
                ->label('Customer Name'),
            ExportColumn::make('email')
                ->label('Email'),
            ExportColumn::make('phone')
                ->label('Phone'),
            ExportColumn::make('address')
                ->label('Address'),
            ExportColumn::make('invoice_due')
                ->getStateUsing(function (Customer $record) {
                    return number_format($record->orders_sum_due ?? 0, 2).' TK';
                })
                ->label('Invoice Due'),
            ExportColumn::make('direct_due')
                ->label('Direct Due')
                ->getStateUsing(function (Customer $record) {
                    return number_format(abs($record->wallet ?? 0), 2).' TK';
                }),
            ExportColumn::make('total_due')
                ->label('Total Due')
                ->getStateUsing(function (Customer $record) {
                    return number_format((abs($record->orders_sum_due ?? 0) + abs($record->wallet ?? 0)), 2).' TK';
                }
                ),

        ];
    }

    public function getFormattedRecord(Customer $record): array
    {
        return [
            'customer_name' => $record->customer_name,
            'email' => $record->email,
            'phone' => $record->phone,
            'address' => $record->address,
            'invoice_due' => number_format($record->orders_sum_due ?? 0, 2).' TK',
            'direct_due' => number_format(abs($record->wallet ?? 0), 2).' TK',
            'total_due' => number_format((abs($record->orders_sum_due ?? 0) + abs($record->wallet ?? 0)), 2).' TK',
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your customer due report export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
