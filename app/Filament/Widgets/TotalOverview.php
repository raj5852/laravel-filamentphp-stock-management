<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Purchase;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
// use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class TotalOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 3;

    protected function getStats(): array
    {

        $totalSold = Order::query()->sum('receivable');

        $totalPurchased = Purchase::query()->sum('payable');

        $total_sold_purchase_cost = OrderItem::query()
            ->whereHas('order')
            ->sum('purchase_cost');

        return [

            Stat::make('Total Sold ', 'TK '.number_format($totalSold, 1))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->iconColor('dark'),
            Stat::make('Total Purchased ', 'TK '.number_format($totalPurchased, 1))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->iconColor('dark'),
            Stat::make('Total Profit', 'TK '.number_format($totalSold - $total_sold_purchase_cost, 1))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->iconColor('dark'),

        ];
    }
}
