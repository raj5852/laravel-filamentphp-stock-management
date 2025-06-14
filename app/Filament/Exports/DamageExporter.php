<?php

namespace App\Filament\Exports;

use App\Models\Damage;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class DamageExporter extends Exporter
{
    protected static ?string $model = Damage::class;

    public static function getColumns(): array
    {
        return [

            ExportColumn::make('product.product_name')
                ->label('Product Name'),

            ExportColumn::make('date')
                ->label('Date')
                ->formatStateUsing(fn ($state) => $state ? date('Y-m-d', strtotime($state)) : null),

            ExportColumn::make('total_in_text')
                ->label('Total Damage'),

            ExportColumn::make('note')
                ->label('Note'),

        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your damage export has completed and '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
