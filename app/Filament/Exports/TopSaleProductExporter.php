<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TopSaleProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('product_name'),
            ExportColumn::make('quantity')
                ->getStateUsing(fn ($record) => $record->quantity ?? 0),
            ExportColumn::make('total_sale')
                ->getStateUsing(fn ($record) => $record->total_sale ?? 0),
            ExportColumn::make('sale_amount')
                ->getStateUsing(fn ($record) => number_format($record->sale_amount ?? 0, 2, '.', '').' TK'),
        ];
    }

    public static function getEloquentQuery()
    {
        return parent::getEloquentQuery()->orderBy('quantity', 'desc');
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
