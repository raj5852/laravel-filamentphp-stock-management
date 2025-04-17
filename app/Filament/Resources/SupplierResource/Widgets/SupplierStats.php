<?php

namespace App\Filament\Resources\SupplierResource\Widgets;

use App\Models\Supplier;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SupplierStats extends BaseWidget
{
    public int $supplierId;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {

        $supplier = Supplier::query()->withSum('purchases', 'payable')->withSum('purchases', 'paid')->withSum('purchases', 'due')->find($this->supplierId);

        return [
            Stat::make('Total Purchase', number_format($supplier?->purchases_sum_payable, 2)),
            Stat::make('Total Paid', number_format($supplier?->purchases_sum_paid, 2)),
            Stat::make('Total Due', number_format($supplier?->purchases_sum_due, 2)),
            Stat::make('Information', '')->description($supplier->address),
        ];
    }
}
