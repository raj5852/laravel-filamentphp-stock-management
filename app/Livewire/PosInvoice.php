<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use Livewire\Component;

class PosInvoice extends Component
{
    public $record;


    function mount($record){
        $this->record = Order::findOrFail($record);
    }

    public function render()
    {
        $setting = Setting::query()->first();
        $customer = Customer::query()->withSum('orders', 'due')->find($this->record->customer_id);
        $order = Order::query()->with('orderitems', 'returnlist')->find($this->record->id);

        return view('livewire.pos-invoice', compact('setting', 'customer', 'order'));

    }
}