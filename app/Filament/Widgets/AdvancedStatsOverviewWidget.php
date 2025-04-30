<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;

class AdvancedStatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $today = today();
        $today_sold = Order::where('order_date', $today)->sum('receivable');

        return [
            Stat::make('Today Sold', 'TK '.number_format($today_sold, 2))
                ->icon('heroicon-o-user')
                ->iconBackgroundColor('success')
                ->description('The users in this period')
                ->descriptionColor('success')
                ->iconColor('success'),
        ];
    }
}
