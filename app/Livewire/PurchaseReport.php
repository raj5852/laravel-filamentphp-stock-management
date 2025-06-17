<?php

namespace App\Livewire;

use App\Models\Purchase;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class PurchaseReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $supplierId;

    public function mount($supplierId)
    {
        $this->supplierId = $supplierId;
    }

    public function table(Table $table)
    {
        return $table
            ->query(Purchase::query()->latest()
                ->where('supplier_id', $this->supplierId)
                ->withCount('purchaseitems'))
            ->heading(new HtmlString('<h2 style="font-size:23px; font-weight:bold">Purchase Report</h2>'))
            ->columns([
                TextColumn::make('data')->label('Purchases Date.')->getStateUsing(function ($record) {
                    return Carbon::parse($record->purchase_date)->format('d M Y');
                }),
                TextColumn::make('purchaseitems_count')->label('Total Item'),
                TextColumn::make('payable')->label('Total Bill')->getStateUsing(function ($record) {
                    return number_format($record->payable, 2, '.', '') . ' Tk';
                }),
                TextColumn::make('paid')->label('Payed')->getStateUsing(function ($record) {
                    return number_format($record->paid, 2, '.', '') . ' TK';
                }),
                TextColumn::make('due')->label('Due')->getStateUsing(function ($record) {
                    return number_format($record->due, 2, '.', '') . ' TK';
                }),

            ])
            ->paginated([100]);
    }

    public function render()
    {
        return view('livewire.purchase-report');
    }
}
