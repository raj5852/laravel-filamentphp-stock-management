<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Purchase;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Carbon;
use Livewire\Component;

class DailyReport extends Component implements HasForms
{
    use InteractsWithForms;

    public $start_date;

    public $end_date;

    public function mount()
    {
        $this->start_date = Carbon::now()->startOfMonth()->toDateString();
        $this->end_date = Carbon::now()->endOfMonth()->toDateString();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    DatePicker::make('start_date')
                        ->placeholder('Enter start Date')
                        ->native(false),
                    DatePicker::make('end_date')
                        ->placeholder('Enter end Date')
                        ->native(false),
                ])
                    ->columns(2),
            ]);
    }

    public function filter()
    {
        $this->start_date = $this->start_date;
        $this->end_date = $this->end_date;
    }

    public function render()
    {
        $start = $this->start_date
            ? Carbon::parse($this->start_date)->startOfDay()
            : Carbon::now()->startOfMonth();

        $end = $this->end_date
            ? Carbon::parse($this->end_date)->endOfDay()
            : Carbon::now()->endOfMonth();

        $dates = collect();
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dates->push($date->format('Y-m-d'));
        }

        // Step 2: Get order (sales) data with profit
        $orders = Order::query()
            ->selectRaw('DATE(order_date) as date, SUM(receivable) as sell_amount, SUM(profit) as profit')
            ->whereBetween('order_date', [$start, $end])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Step 3: Get purchase data
        $purchases = Purchase::query()
            ->selectRaw('DATE(purchase_date) as date, SUM(payable) as purchase_amount')
            ->whereBetween('purchase_date', [$start, $end])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Step 4: Combine into final report
        $results = $dates->map(function ($date) use ($orders, $purchases) {
            $order = $orders[$date] ?? null;
            $purchase = $purchases[$date] ?? null;

            return [
                'date' => $date,
                'sell_amount' => $order->sell_amount ?? 0,
                'purchase_amount' => $purchase->purchase_amount ?? 0,
                'profit' => $order->profit ?? 0,
            ];
        });

        // dd($results);

        return view('livewire.daily-report', compact('results'));
    }
}
