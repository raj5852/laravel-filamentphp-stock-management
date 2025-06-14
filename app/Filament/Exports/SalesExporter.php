<?php

namespace App\Filament\Exports;

use App\Models\Order;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class SalesExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('invoiceno')
                ->label('Invoice No'),
                
            ExportColumn::make('customer.customer_name')
                ->label('Customer'),
                
            ExportColumn::make('order_date')
                ->label('Date'),
                
            ExportColumn::make('receivable')
                ->label('Total Amount')
                ->formatStateUsing(fn ($state) => number_format($state ?: 0, 2) . ' TK'),
                
            ExportColumn::make('paid')
                ->label('Paid Amount')
                ->formatStateUsing(fn ($state) => number_format($state ?: 0, 2) . ' TK'),
                
            ExportColumn::make('due')
                ->label('Due Amount')
                ->formatStateUsing(fn ($state) => number_format($state ?: 0, 2) . ' TK'),
                
            ExportColumn::make('orderitems_sum_purchase_cost')
                ->label('Purchase Cost')
                ->formatStateUsing(fn ($state) => number_format($state ?: 0, 2) . ' TK'),
                
            ExportColumn::make('profit')
                ->label('Profit')
                ->getStateUsing(function (Order $record) {
                    return number_format(($record->receivable - $record->orderitems_sum_purchase_cost) ?: 0, 2) . ' TK';
                }),
                
            ExportColumn::make('status')
                ->label('Payment Status')
                ->getStateUsing(function (Order $record) {
                    return $record->receivable == $record->paid ? 'Paid' : 'Unpaid';
                }),
                
            ExportColumn::make('products')
                ->label('Products')
                ->getStateUsing(function (Order $record) {
                    return $record->orderitems->map(function ($item) {
                        $productName = $item->product->product_name ?? 'N/A';
                        $productCode = $item->product->product_code ?? 'N/A';
                        return "{$productName} | {$productCode}";
                    })->implode(', ');
                }),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your sales export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
