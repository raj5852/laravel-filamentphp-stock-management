<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class TodaySummery extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = today();
        $today_sold = Order::where('order_date', $today)->sum('receivable');
        $today_sold_purchase_cost = OrderItem::query()
            ->whereHas('order', function ($query) use ($today) {
                $query->where('order_date', $today);
            })
            ->sum('purchase_cost');

        return [
            Stat::make('Today Sold', 'TK '.number_format($today_sold, 1))
                ->icon('heroicon-o-user')
                ->iconBackgroundColor('success')
                ->descriptionColor('success')
                ->iconColor('success'),

            Stat::make('Today Sold - Purchase Cost', 'TK '.number_format($today_sold_purchase_cost, 1))
                ->icon('heroicon-o-user')
                ->iconBackgroundColor('success')
                ->descriptionColor('success')
                ->iconColor('success'),

            Stat::make('Today Sell Profit', 'TK '.number_format($today_sold - $today_sold_purchase_cost, 1))
                ->icon('heroicon-o-user')
                ->iconBackgroundColor('success')
                ->descriptionColor('success')
                ->iconColor('success'),

        ];
    }
}
