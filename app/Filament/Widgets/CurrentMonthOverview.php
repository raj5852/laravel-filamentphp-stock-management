<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Purchase;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
// use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Carbon;

class CurrentMonthOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $currentMonthYear = Carbon::now()->format('F Y');

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString(); // "2025-05-01"
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();     // "2025-05-31"

        $soldInCurrentMonth = Order::query()->whereBetween('order_date', [$startOfMonth, $endOfMonth])->sum('receivable');

        $purchasedInCurrentMonth = Purchase::query()->whereBetween('purchase_date', [$startOfMonth, $endOfMonth])->sum('payable');
        $current_month_sold_purchase_cost = OrderItem::query()
            ->whereHas('order', function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('order_date', [$startOfMonth, $endOfMonth]);
            })
            ->sum('purchase_cost');

        $expenseInCurrentMonth = Expense::query()->whereBetween('date', [$startOfMonth, $endOfMonth])->sum('amount');

        return [
            Stat::make('Sold in '.$currentMonthYear, 'TK '.number_format($soldInCurrentMonth, 1))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->descriptionColor('success')
                ->iconColor('warning'),
            Stat::make('Purchased - in '.$currentMonthYear, 'TK '.number_format($purchasedInCurrentMonth, 1))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->descriptionColor('success')
                ->iconColor('warning'),

            Stat::make('Expense in '.$currentMonthYear, 'TK '.number_format($expenseInCurrentMonth, 1))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->descriptionColor('success')
                ->iconColor('warning'),

            Stat::make('Profit '.$currentMonthYear, 'TK '.number_format($soldInCurrentMonth - $current_month_sold_purchase_cost, 1))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->descriptionColor('success')
                ->iconColor('warning'),

        ];
    }
}
