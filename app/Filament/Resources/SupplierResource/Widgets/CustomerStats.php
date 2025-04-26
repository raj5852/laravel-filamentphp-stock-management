<?php

namespace App\Filament\Resources\SupplierResource\Widgets;

use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerStats extends BaseWidget
{
    public int $customerId;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $customer = Customer::query()
            ->withSum('orders', 'receivable')
            ->withSum('orders', 'paid')
            ->withSum('orders', 'due')
            ->find($this->customerId);

        return [
            Stat::make('Total Buy', number_format($customer?->orders_sum_receivable, 2)),
            Stat::make('Total Paid', number_format($customer?->orders_sum_paid, 2)),
            Stat::make('Total Due', number_format($customer?->orders_sum_due, 2)),
            Stat::make('Information', '')->description($customer->address),
        ];
    }
}
