<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Supplier;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
// use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class TotalSummery extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 7;

    protected function getStats(): array
    {

        $total_customer = Customer::where('is_default', '!=', 1)->count();
        $total_supplier = Supplier::where('is_default', '!=', 1)->count();
        $total_invoice = Order::count();
        $total_product = Product::count();

        return [
            Stat::make('Total Customer ', number_format($total_customer, 0))
                ->icon('heroicon-o-user-group')
                ->iconBackgroundColor('success')
                ->iconColor('dark'),

            Stat::make('Total Supplier ', number_format($total_supplier, 0))
                ->icon('heroicon-o-user-group')
                ->iconBackgroundColor('success')
                ->iconColor('dark'),
            Stat::make('Total Invoices', number_format($total_invoice, 0))
                ->icon('heroicon-o-document')
                ->iconBackgroundColor('success')
                ->iconColor('dark'),
            Stat::make('Total Product', number_format($total_product, 0))
                ->icon('heroicon-o-inbox-stack')
                ->iconBackgroundColor('success')
                ->iconColor('dark'),
        ];
    }
}
