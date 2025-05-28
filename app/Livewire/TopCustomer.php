<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Product;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Carbon;
use Livewire\Component;

class TopCustomer extends Component implements HasForms
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
        $startOfMonth = $this->start_date;
        $endOfMonth = $this->end_date;

        $datas = Customer::query()
            ->where('is_default', '!=', 1)
            ->withSum(['orders as total_sell' => function ($query) use ($startOfMonth, $endOfMonth) {
                if (!empty($startOfMonth) && !empty($endOfMonth)) {
                    $query->whereBetween('order_date', [$startOfMonth, $endOfMonth]);
                }
            }], 'receivable')
            ->orderBy('total_sell', 'desc')
            ->get();
        return view('livewire.top-customer', compact('datas'));
    }
}
