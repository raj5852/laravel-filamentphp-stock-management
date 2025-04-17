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

class PurchasePaymentReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $supplierId;

    public function mount($supplierId)
    {
        $this->supplierId = $supplierId;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                History::query()->latest()
                    ->withwhereHas('purchase', function ($query) {
                        $query->where('supplier_id', $this->supplierId)->select('id', 'supplier_id', 'purchase_date');
                    })
                    ->with('account:id,name')
            )
            ->heading(new HtmlString("<h2 style='font-size:23px; font-weight:bold'>Purchase Payment Report</h2>"))
            ->columns([
                TextColumn::make('purchase.purchase_date')->date(),
                TextColumn::make('amount')->label('Pay Amount'),
                TextColumn::make('account.name')->label('Transaction Account'),
                TextColumn::make('Payment Type')->default('pay'),
            ])
            ->paginated([100]);

    }

    public function render()
    {
        return view('livewire.purchase-payment-report');
    }
}
