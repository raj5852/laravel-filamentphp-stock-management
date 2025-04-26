<?php

namespace App\Livewire;

use App\Models\Order;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class CustomerReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $customerId;

    public function mount($customerId)
    {
        $this->customerId = $customerId;
    }

    public function table(Table $table)
    {
        return $table
            ->query(Order::query()->latest()
                ->where('customer_id', $this->customerId)
                ->withCount('orderitems'))
            ->heading(new HtmlString('<h2 style="font-size:23px; font-weight:bold">Sale Report</h2>'))
            ->columns([
                TextColumn::make('data')->label('Purchases Date.')->getStateUsing(function ($record) {
                    return Carbon::parse($record->order_date)->format('d M Y');
                }),
                TextColumn::make('orderitems_count')->label('Total Item'),
                TextColumn::make('receivable')->label('Total Bill')->getStateUsing(function ($record) {
                    return number_format($record->receivable, 2, '.', '').' Tk';
                }),
                TextColumn::make('paid')->label('Payed')->getStateUsing(function ($record) {
                    return number_format($record->paid, 2, '.', '').' TK';
                }),
                TextColumn::make('due')->label('Due')->getStateUsing(function ($record) {
                    return number_format($record->due, 2, '.', '').' TK';
                }),

            ])
            ->paginated([100]);
    }

    public function render()
    {
        return view('livewire.customer-report');
    }
}
