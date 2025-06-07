<?php

namespace App\Livewire;

use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class ProfitLossReport extends Component implements HasForms
{
    use InteractsWithForms;

    public $start_date;

    public $end_date;

    public function mount()
    {
        // Set the default start date to the first day of the current month
        $this->start_date = now()->startOfYear()->format('Y-m');
        // Optionally set the default end date (e.g., to the last day of the current month)
        $this->end_date = now()->endOfMonth()->format('Y-m');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    DatePicker::make('start_date')
                        ->label('Enter Start Month')
                        ->default($this->start_date) // Set default using the Livewire property
                        ->extraInputAttributes(['type' => 'month']),

                    DatePicker::make('end_date')
                        ->label('Enter End Month')
                        ->default($this->end_date) // Set default using the Livewire property
                        ->extraInputAttributes(['type' => 'month']),
                ])
                    ->columns(2),
            ]);
    }

    public function filter()
    {

        $data = [
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ];

        $validator = Validator::make($data, [
            'start_date' => ['required', 'date_format:Y-m'],
            'end_date' => ['required', 'date_format:Y-m'],
        ]);

        if ($validator->fails()) {
            Notification::make()
                ->danger()
                ->title('Please Select Valid month')
                ->send();

            return false;
        }
    }

    public function render()
    {
        $startDate = \Carbon\Carbon::parse($this->start_date)->endOfMonth()->toDateString();
        $endDate = \Carbon\Carbon::parse($this->end_date)->endOfMonth()->toDateString();

        $tenantId = auth()->user()->tenant_id;

        // First, get the expenses by year and month
        $expenses = DB::table('expenses')
            ->where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select(
                DB::raw('YEAR(date) as year'),
                DB::raw('MONTH(date) as month'),
                DB::raw('SUM(amount) as total_expenses')
            )
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(function ($item) {
                return $item->year.'-'.$item->month;
            });

        // Then get the order data
        $orderitems = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.tenant_id', $tenantId)
            ->whereBetween('orders.order_date', [$startDate, $endDate])
            ->select(
                DB::raw('YEAR(orders.order_date) as year'),
                DB::raw('MONTH(orders.order_date) as month'),
                DB::raw('SUM(orders.receivable) as sales'),
                DB::raw('SUM(order_items.purchase_cost) as cost_of_goods_sold'),
                DB::raw('SUM(orders.profit) as gross_profit')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Add expenses and calculate net profit
        foreach ($orderitems as $item) {
            $key = $item->year.'-'.$item->month;
            $item->expenses = isset($expenses[$key]) ? $expenses[$key]->total_expenses : 0;
            $item->net_profit = $item->gross_profit - $item->expenses;
        }

        return view('livewire.profit-loss-report', compact('orderitems'));
    }
}
