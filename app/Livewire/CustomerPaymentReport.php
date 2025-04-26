<?php

namespace App\Livewire;

use App\Models\History;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class CustomerPaymentReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $customerId;

    public function mount($customerId)
    {
        $this->customerId = $customerId;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                History::query()
                    ->latest()
                    ->withwhereHas('order', function ($query) {
                        $query->where('customer_id', $this->customerId)->select('id', 'customer_id', 'order_date');
                    })
                    ->with('account:id,name')
            )
            ->heading(new HtmlString("<h2 style='font-size:23px; font-weight:bold'>Sales Payment Report</h2>"))
            ->columns([
                TextColumn::make('order.order_date')->date(),
                TextColumn::make('amount')->label('Pay Amount'),
                TextColumn::make('account.name')->label('Transaction Account'),
                TextColumn::make('Payment Type')->default('pay'),
            ])
            ->paginated([100]);

    }

    public function render()
    {
        return view('livewire.customer-payment-report');
    }
}
