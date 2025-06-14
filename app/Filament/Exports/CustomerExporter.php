<?php

namespace App\Filament\Exports;

use App\Models\Customer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CustomerExporter extends Exporter
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

            ExportColumn::make('orders_sum_receivable')
                ->label('Receivable')
                ->state(function (Customer $customer) {
                    return number_format($customer->orders_sum_receivable ?: 0, 2).' TK';
                }),

            ExportColumn::make('orders_sum_paid')
                ->label('Paid')
                ->state(function (Customer $customer) {
                    return number_format($customer->orders_sum_paid ?: 0, 2).' TK';
                }),

            ExportColumn::make('orders_sum_due')
                ->label('Sale Due')
                ->state(function (Customer $customer) {
                    return number_format($customer->orders_sum_due ?: 0, 2).' TK';
                }),

            ExportColumn::make('wallet_balance')
                ->label('Wallet Balance')
                ->state(function (Customer $customer) {
                    return number_format(abs($customer->wallet), 1).' TK';
                }),

            ExportColumn::make('total_due')
                ->label('Total Due')
                ->state(function (Customer $customer) {
                    $balance = 0;

                    if ($customer->wallet <= 0) {
                        $balance = abs($customer->wallet);
                    }

                    return number_format(abs($balance) + ($customer->orders_sum_due ?: 0), 2).' TK';
                }),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your customer export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
