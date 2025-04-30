<?php

namespace App\Livewire;

use App\Models\Customer;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CustomerLedger extends Component implements HasForms
{
    use InteractsWithForms;

    public $customer_id;

    public $start_date;

    public $end_date;

    public $datas = [];

    public function mount()
    {
        $this->customer_id = request('customer_id');
        if ($this->customer_id) {
            $this->filter();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    Select::make('customer_id')
                        ->label('Customer')
                        ->options(Customer::query()->where('is_default', '!=', 1)->pluck('customer_name', 'id'))
                        ->searchable()
                        ->required(),
                    DatePicker::make('start_date')
                        ->placeholder('Enter start Date')
                        ->native(false),
                    DatePicker::make('end_date')
                        ->placeholder('Enter end Date')
                        ->native(false),
                ])
                    ->columns(3),
            ]);
    }

    public function filter()
    {
        if ($this->customer_id == '') {
            Notification::make()
                ->danger()
                ->title('Please Select Customer')
                ->send();

            return;
        }

        if ($this->end_date != '' && $this->start_date == '') {
            Notification::make()
                ->danger()
                ->title('Please Select Start Date')
                ->send();

            return;
        }
        if ($this->end_date == '' && $this->start_date != '') {
            Notification::make()
                ->danger()
                ->title('Please Select End Date')
                ->send();

            return;
        }
        $tenantId = auth()->user()->tenant_id;
        $customerId = $this->customer_id;
        Customer::query()->where('is_default', '!=', 1)->findOrFail($customerId);

        $datas = DB::table('orders')
            ->where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->when($this->start_date != null && $this->end_date != null, function ($query) {
                return $query->whereBetween('order_date', [$this->start_date, $this->end_date]);
            })
            ->select('id', 'order_date as date', 'receivable as amount', 'total_amount', 'created_at', DB::raw('"order" as type'), 'invoiceno as particulars')
            ->union(
                DB::table('histories')
                    ->where('tenant_id', $tenantId)
                    ->where('customer_id', $customerId)
                    ->when($this->start_date != null && $this->end_date != null, function ($query) {
                        return $query->whereBetween('date', [$this->start_date, $this->end_date]);
                    })
                    ->select('id', 'date', 'amount', 'total_amount', 'created_at', DB::raw('"history" as type'), DB::raw('"Received from Customer" as particulars'))
            )
            ->orderBy('created_at', 'asc')
            ->get();

        $this->datas = $datas;
    }

    public function render()
    {
        return view('livewire.customer-ledger');
    }
}
