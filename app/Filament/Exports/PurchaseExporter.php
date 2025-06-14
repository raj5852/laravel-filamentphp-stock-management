<?php

namespace App\Filament\Exports;

use App\Models\Purchase;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PurchaseExporter extends Exporter
{
    protected static ?string $model = Purchase::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('billno')
                ->label('Bill No.'),
                
            ExportColumn::make('supplier.supplier_name')
                ->label('Supplier'),
                
            ExportColumn::make('purchase_date')
                ->label('Date'),
                
            ExportColumn::make('payable')
                ->label('Payable')
                ->formatStateUsing(fn ($state) => number_format($state, 2, '.', '') . ' Tk'),
                
            ExportColumn::make('paid')
                ->label('Paid Amount')
                ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', '') . ' Tk'),
                
            ExportColumn::make('due')
                ->label('Due Amount')
                ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', '') . ' Tk'),
                
            ExportColumn::make('products')
                ->label('Products')
                ->getStateUsing(function (Purchase $record) {
                    return $record->purchaseItems->map(function ($item) {
                        $productName = $item->product->product_name ?? 'N/A';
                        $productCode = $item->product->product_code ?? 'N/A';
                        return "{$productName} | {$productCode}";
                    })->implode(', ');
                }),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your purchase export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
