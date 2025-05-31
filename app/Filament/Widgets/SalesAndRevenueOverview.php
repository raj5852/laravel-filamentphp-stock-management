<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class SalesAndRevenueOverview extends ChartWidget
{
    protected static ?string $heading = 'Last 30-Day Sales & Revenue Overview';

    protected int|string|array $columnSpan = 'full'; // Make the widget full-width

    protected static ?int $sort = 4;

    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $data = $this->getLast30DaysStaticData();

        return [
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => $data['salesValues'],
                    'backgroundColor' => 'rgba(75, 192, 192, 0.5)',
                    'borderColor' => 'rgb(75, 192, 192)',
                    'tension' => 0.1,
                ],
                [
                    'label' => 'Revenue',
                    'data' => $data['revenueValues'],
                    'backgroundColor' => 'rgba(255, 159, 64, 0.5)',
                    'borderColor' => 'rgb(255, 159, 64)',
                    'tension' => 0.1,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'responsive' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }

    private function getLast30DaysStaticData(): array
    {
        $startDate = Carbon::today()->subDays(29);
        $endDate = Carbon::today();

        // Retrieve the receivable (sales) and profit (revenue) data for the last 30 days grouped by date
        $orders = \DB::table('orders')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->selectRaw('DATE(order_date) as order_date, SUM(receivable) as total_receivable, SUM(profit) as total_profit')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->groupBy('order_date')
            ->orderBy('order_date')
            ->get();

        $labels = [];
        $salesValues = [];
        $revenueValues = [];

        foreach (range(0, 29) as $daysAgo) {
            $date = $startDate->copy()->addDays($daysAgo);
            $labels[] = $date->format('M d'); // Format the date as "May 01"

            // Find the data for this specific date or set it to 0 if not found
            $order = $orders->firstWhere('order_date', $date->toDateString());
            $salesValues[] = $order ? (float) $order->total_receivable : 0;
            $revenueValues[] = $order ? (float) $order->total_profit : 0;
        }

        return [
            'labels' => $labels,
            'salesValues' => $salesValues,
            'revenueValues' => $revenueValues,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
