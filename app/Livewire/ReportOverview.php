<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReportOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    public $startDate;

    public $endDate;

    public $isFilter;

    public function mount($startDate = null, $endDate = null, $isFilter = true)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->isFilter = $isFilter;
    }

    protected function getStats(): array
    {

        $sale_amount = Order::query()
            ->when($this->isFilter, function ($q) {
                $q->whereBetween('order_date', [$this->startDate, $this->endDate]);
            })
            ->sum('receivable');

        $purchaseCost = OrderItem::query()
            ->when($this->isFilter, function ($q) {
                $q->whereHas('order', function ($query) {
                    $query->whereBetween('order_date', [$this->startDate, $this->endDate]);
                });
            })
            ->sum('purchase_cost');

        $sell_profit = $sale_amount - $purchaseCost;

        return [
            Stat::make('Sale Amount', number_format($sale_amount, 2).' TK'),
            Stat::make('Purchase Cost', number_format($purchaseCost, 2).' TK'),
            Stat::make('Sell Profit', number_format($sell_profit, 2).' TK'),
        ];
    }
}
