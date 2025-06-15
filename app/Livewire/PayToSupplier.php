<?php

namespace App\Livewire;

use App\Filament\Exports\PayToSupplierExporter;
use App\Models\History;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class PayToSupplier extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $startDate;

    public $endDate;

    public $isFilter = true;

    public function mount($startDate = null, $endDate = null, $isFilter = true)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->isFilter = $isFilter;
    }

    public function table(Table $table)
    {
        return $table
            ->query(
                History::query()
                    ->where('supplier_id', '!=', '')
                    ->when($this->isFilter, function ($q) {
                        $q->withwhereHas('payment', function ($subq) {
                            $subq->whereBetween('payment_date', [$this->startDate, $this->endDate])
                                ->select('id', 'payment_date');
                        });
                    })
                    ->with('supplier:id,supplier_name')
                    ->select('id', 'supplier_id', 'date', 'amount', 'payment_id')
            )
            ->heading(new HtmlString('<h2 style="font-size:23px; font-weight:bold">Pay to Supplier</h2>'))
            ->filters($this->setFilter(), layout: FiltersLayout::AboveContent)
            ->columns([
                TextColumn::make('supplier.supplier_name')->label('Supplier Name'),
                TextColumn::make('payment.payment_date')->date()->label('Payment Date'),
                TextColumn::make('amount')->label('Amount')->getStateUsing(function ($record) {
                    return number_format($record->amount, 2, '.', '');
                })
                    ->summarize(
                        Sum::make()->formatStateUsing(fn ($state) => number_format($state, 2, '.', '').' Tk')->label('Total')
                    ),
            ])
            ->filtersFormColumns(2)
            ->headerActions([
                ExportAction::make()
                    ->exporter(PayToSupplierExporter::class)
                    ->columnMapping(false)
                    ->label('Export Pay to Supplier')
                    ->modalHeading('Export Pay to Supplier')
                    ->modifyQueryUsing(function (Builder $query) {
                        $query->where('supplier_id', '!=', '')
                            ->when($this->isFilter, function ($q) {
                                $q->withwhereHas('payment', function ($subq) {
                                    $subq->whereBetween('payment_date', [$this->startDate, $this->endDate])
                                        ->select('id', 'payment_date');
                                });
                            })
                            ->with('supplier:id,supplier_name')
                            ->select('id', 'supplier_id', 'date', 'amount', 'payment_id');
                    }),
            ])
            ->paginated([10, 25, 50, 100]);
    }

    public function setFilter()
    {
        if ($this->isFilter == true) {
            return [];
        }

        return [
            Filter::make('startDate')
                ->label('')
                ->form([
                    DatePicker::make('startDateFilter')
                        ->label('')
                        ->placeholder('Select Start Date')
                        ->native(false),
                ])
                ->query(function ($query, array $data) {
                    if (isset($data['startDateFilter'])) {
                        $query->whereHas('payment', function ($subq) use ($data) {
                            $subq->where('payment_date', '>=', $data['startDateFilter']);
                        });
                    }
                }),
            Filter::make('endDate')
                ->label('')
                ->form([
                    DatePicker::make('endDate')
                        ->label('')
                        ->placeholder('Select End Date')
                        ->native(false),
                ])
                ->query(function ($query, array $data) {
                    if (isset($data['endDate'])) {
                        $query->whereHas('payment', function ($subq) use ($data) {
                            $subq->where('payment_date', '<=', $data['endDate']);
                        });
                    }
                }),

        ];
    }

    public function render()
    {
        return view('livewire.pay-to-supplier');
    }
}
