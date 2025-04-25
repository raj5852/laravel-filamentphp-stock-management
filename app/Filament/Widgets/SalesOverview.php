<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $receivable = Order::query()
            ->where('order_date', today())
            ->sum('receivable');

        $paid = Order::query()
            ->where('order_date', today())
            ->sum('paid');

        $purchaseCost = OrderItem::query()->whereHas('order', function ($query) {
            $query->where('order_date', today());
        })
            ->sum('purchase_cost');

        $total_receivable = Order::query()
            ->sum('receivable');

        return [
            Stat::make('Sold Today', number_format($receivable, 2).' TK'),
            Stat::make('Today Received', number_format($paid, 2).' TK'),
            Stat::make('Today Profit', number_format($receivable - $purchaseCost, 2).' TK'),
            Stat::make('Total Sold', number_format($total_receivable, 2).' TK'),
        ];
    }
}
