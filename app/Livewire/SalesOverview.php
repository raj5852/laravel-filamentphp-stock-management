<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class SalesOverview extends BaseWidget
{
    public ?Carbon $date = null;

    public function mount($date = null)
    {
        $this->date = $date ? Carbon::parse($date) : today();
    }

    protected function getStats(): array
    {
        $receivable = Order::query()
            ->where('order_date', $this->date)
            ->sum('receivable');

        $paid = Order::query()
            ->where('order_date', $this->date)
            ->sum('paid');

        $purchaseCost = OrderItem::query()->whereHas('order', function ($query) {
            $query->where('order_date', $this->date);
        })->sum('purchase_cost');

        $total_receivable = Order::query()
            ->sum('receivable');

        return [
            Stat::make('Sold Today', number_format($receivable, 2).' TK'),
            Stat::make('Today '.' Received', number_format($paid, 2).' TK'),
            Stat::make('Today '.' Profit', number_format($receivable - $purchaseCost, 2).' TK'),
            Stat::make('Total Sold', number_format($total_receivable, 2).' TK'),
        ];
    }
}
