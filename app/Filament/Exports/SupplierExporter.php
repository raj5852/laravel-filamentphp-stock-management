<?php

namespace App\Filament\Exports;

use App\Models\Supplier;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class SupplierExporter extends Exporter
{
    protected static ?string $model = Supplier::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('supplier_name')
                ->label('Name'),
            ExportColumn::make('email')
                ->label('Email'),
            ExportColumn::make('phone')
                ->label('Phone'),
            ExportColumn::make('address')
                ->label('Address'),
            ExportColumn::make('purchases_sum_payable')
                ->label('Payable')
                ->state(function (Supplier $record): string {
                    return number_format($record->purchases_sum_payable ?: 0, 2, '.', '') . ' TK';
                }),
            ExportColumn::make('purchases_sum_paid')
                ->label('Paid')
                ->state(function (Supplier $record): string {
                    return number_format($record->purchases_sum_paid ?: 0, 2, '.', '') . ' TK';
                }),
            ExportColumn::make('purchases_sum_due')
                ->label('Due')
                ->state(function (Supplier $record): string {
                    return number_format($record->purchases_sum_due ?: 0, 2, '.', '') . ' TK';
                }),
            ExportColumn::make('wallet_balance')
                ->label('Wallet Balance')
                ->state(function (Supplier $record): string {
                    return number_format(abs($record->wallet), 2, '.', '') . ' TK';
                }),
            ExportColumn::make('total_due')
                ->label('Total Due')
                ->state(function (Supplier $record): string {
                    $balance = 0;
                    
                    if ($record->wallet <= 0) {
                        $balance = abs($record->wallet);
                    }
                    
                    return number_format(abs($balance) + $record->purchases_sum_due ?: 0, 2, '.', '') . ' TK';
                }),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your supplier export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
