<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\HtmlString;

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

        $todayExpense = Expense::where('date', $today)->sum('amount');

        $user = User::find(auth()->user()->tenant_id);

        return [

            Stat::make('SMS Balance', $user->sms_count)
                ->icon('heroicon-o-chat-bubble-left-right')
                ->iconBackgroundColor('primary')
                ->iconColor('white')
                ->extraAttributes([
                    'class' => 'relative',
                ])
            // ->description(new HtmlString(
            //     '<div class="flex justify-between items-center w-full">

            //         <a href="#" class="px-3 py-1 text-xs font-medium text-white bg-primary-600 rounded-full hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Buy More</a>
            //     </div>'
            // ))
            ,

            Stat::make('Subscription Expires', $user->expires_at)
                ->icon('heroicon-o-calendar')
                ->iconBackgroundColor('warning')
                ->iconColor('white')
                ->extraAttributes([
                    'class' => 'relative',
                ])
            // ->description(new HtmlString(
            //     '<div class="flex justify-between items-center w-full">

            //         <a href="#" class="px-3 py-1 text-xs font-medium text-white bg-warning-600 rounded-full hover:bg-warning-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-warning-500">Renew Now</a>
            //     </div>'
            // ))
            ,

            Stat::make('Today Sold', 'TK '.number_format($today_sold, 1))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->descriptionColor('success')
                ->iconColor('success'),

            Stat::make('Today Sold - Purchase Cost', 'TK '.number_format($today_sold_purchase_cost, 1))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->descriptionColor('success')
                ->iconColor('success'),

            Stat::make('Today Expense', 'TK '.number_format($todayExpense, 1))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->descriptionColor('success')
                ->iconColor('success'),

            Stat::make('Today Sell Profit', 'TK '.number_format($today_sold - ($today_sold_purchase_cost + $todayExpense), 1))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->descriptionColor('success')
                ->iconColor('success'),

        ];
    }
}
