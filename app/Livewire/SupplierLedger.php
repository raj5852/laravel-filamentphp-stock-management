<?php

namespace App\Livewire;

use App\Models\Supplier;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SupplierLedger extends Component implements HasForms
{
    use InteractsWithForms;

    public $supplier_id;

    public $start_date;

    public $end_date;

    public $datas = [];

    public function mount()
    {
        $this->supplier_id = request('supplier_id');
        if ($this->supplier_id) {
            $this->filter();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    Select::make('supplier_id')
                        ->label('Supplier')
                        ->options(Supplier::query()->where('is_default', '!=', 1)->pluck('supplier_name', 'id'))
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
        if ($this->supplier_id == '') {
            Notification::make()
                ->danger()
                ->title('Please Select Supplier')
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
        $supplierId = $this->supplier_id;
        Supplier::query()->where('is_default', '!=', 1)->findOrFail($supplierId);

        $datas = DB::table('purchases')
            ->where('tenant_id', $tenantId)
            ->where('supplier_id', $supplierId)
            ->when($this->start_date != null && $this->end_date != null, function ($query) {
                return $query->whereBetween('purchase_date', [$this->start_date, $this->end_date]);
            })
            ->select('id', 'purchase_date as date', 'payable as amount', 'created_at', DB::raw('"purchase" as type'), 'billno as particulars')
            ->union(
                DB::table('histories')
                    ->where('tenant_id', $tenantId)
                    ->where('supplier_id', $supplierId)
                    ->when($this->start_date != null && $this->end_date != null, function ($query) {
                        return $query->whereBetween('date', [$this->start_date, $this->end_date]);
                    })
                    ->select('id', 'date', 'amount', 'created_at', DB::raw('"history" as type'), 'type as particulars')
            )
            ->union(
                DB::table('opening_balances')
                    ->where('tenant_id', $tenantId)
                    ->where('supplier_id', $supplierId)
                    ->when($this->start_date != null && $this->end_date != null, function ($query) {
                        return $query->whereBetween('created_at', [$this->start_date, $this->end_date]);
                    })
                    ->select('id', 'created_at as date', 'amount', 'created_at', DB::raw('"opening_balance" as type'), 'particulars')
            )
            ->orderBy('created_at', 'asc')
            ->get();
        $this->datas = $datas;
    }

    public function render()
    {
        return view('livewire.supplier-ledger');
    }
}
