<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Supplier;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
// use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class BalanceOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 5;

    protected function getStats(): array
    {

        $orderDue = Order::query()->sum('due');
        $customerReceivableWallet = abs(Customer::query()->where('wallet', '<', 0)->sum('wallet'));
        $supplierReceivableWallet = Supplier::query()->where('wallet', '>=', 0)->sum('wallet');

        $total_receivable = $orderDue + $customerReceivableWallet + $supplierReceivableWallet;

        $purcahseDue = Purchase::query()->sum('due');
        $customerPayableWallet = abs(Customer::query()->where('wallet', '>=', 0)->sum('wallet'));
        $supplierPayableWallet = abs(Supplier::query()->where('wallet', '<', 0)->sum('wallet'));

        $total_Payable = $purcahseDue + $customerPayableWallet + $supplierPayableWallet;

        $total_balance = Account::sum('current_balance');

        return [

            Stat::make('Total Receivable ', 'TK '.number_format($total_receivable, 1))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->iconColor('success'),
            Stat::make('Total Payable', 'TK '.number_format($total_Payable, 1))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->iconColor('success'),
            Stat::make('Total Banalce', 'TK '.number_format($total_balance, 1))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->iconColor('success'),

        ];
    }
}
