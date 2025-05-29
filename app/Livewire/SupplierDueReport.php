<?php

namespace App\Livewire;

use App\Models\Supplier;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;

class SupplierDueReport extends Component implements HasForms
{
    use InteractsWithForms;

    public $supplier_id = null;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    Select::make('supplier_id')
                        ->label('Supplier')
                        ->placeholder('Select Supplier')
                        ->options(Supplier::query()->where('is_default', '!=', '1')->pluck('supplier_name', 'id'))
                        ->searchable(),

                ])
                    ->columns(2),
            ]);
    }

    public function filter()
    {
        $this->supplier_id = $this->form->getState()['supplier_id'];
    }

    public function render()
    {
        $datas = Supplier::query()
            ->when($this->supplier_id, function ($query) {
                return $query->where('id', $this->supplier_id);
            })
            ->withSum('purchases', 'due')
            ->get()
            ->filter(function ($supplier) {
                return ($supplier->purchases_sum_due > 0) || ($supplier->wallet < 0);
            })
            ->map(function ($supplier) {
                $supplier->total_dues = abs($supplier->purchases_sum_due ?: 0) + abs($supplier->wallet ?: 0);

                return $supplier;
            })
            ->sortByDesc(function ($supplier) {
                return $supplier->total_dues;
            })
            ->values();

        return view('livewire.supplier-due-report', compact('datas'));
    }
}
