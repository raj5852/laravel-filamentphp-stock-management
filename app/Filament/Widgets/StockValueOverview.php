<?php

namespace App\Filament\Widgets;

use App\Models\ProductDetail;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
// use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class StockValueOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 6;

    protected function getColumns(): int
    {
        return 2; // Define the number of columns in the widget
    }

    protected function getStats(): array
    {

        $stock_purchase_value = ProductDetail::query()
            ->select(DB::raw('SUM(single_unit_purchase_price * available_stock) as total'))
            ->value('total');

        $stock_sell_value = ProductDetail::query()
            ->select(DB::raw('SUM(single_unit_sale_price * available_stock) as total'))
            ->value('total');

        return [

            Stat::make('Stock - Purchase Value ', 'TK ' . number_format($stock_purchase_value, 1))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->iconColor('primary'),
            Stat::make('Stock - Sell Value', 'TK ' . number_format($stock_sell_value, 1))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->iconColor('primary'),

        ];
    }
}
