<?php

namespace App\Livewire;

use App\Models\Customer;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;

class CustomerDueReport extends Component implements HasForms
{
    use InteractsWithForms;

    public $customer_id = null;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    Select::make('customer_id')
                        ->label('Customer')
                        ->placeholder('Select Customer')
                        ->options(Customer::query()->where('is_default', '!=', 1)->pluck('customer_name', 'id'))
                        ->searchable(),

                ])
                    ->columns(2),
            ]);
    }

    public function filter()
    {
        $this->customer_id = $this->form->getState()['customer_id'];
    }

    public function render()
    {
        $datas = Customer::query()
            ->when($this->customer_id, function ($query) {
                return $query->where('id', $this->customer_id);
            })
            ->withSum('orders', 'due')
            ->get()
            ->filter(function ($customer) {
                return ($customer->orders_sum_due > 0) || ($customer->wallet < 0);
            })
            ->map(function ($customer) {
                $customer->total_dues = abs($customer->orders_sum_due ?: 0) + abs($customer->wallet ?: 0);

                return $customer;
            })
            ->sortByDesc(function ($customer) {
                return $customer->total_dues;
            })
            ->values();

        return view('livewire.customer-due-report', compact('datas'));
    }
}
