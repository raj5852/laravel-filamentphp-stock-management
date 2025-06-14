<?php

namespace App\Livewire;

use App\Filament\Exports\ExpenseReportExporter;
use App\Models\Expense;
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

class ExpenseReport extends Component implements HasForms, HasTable
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
                Expense::query()
                    ->when($this->isFilter, function ($q) {
                        $q->whereBetween('date', [$this->startDate, $this->endDate]);
                    })
                    ->with('expenseCategory:id,name')

            )
            ->heading(new HtmlString('<h2 style="font-size:23px; font-weight:bold">Expense</h2>'))
            ->filters($this->setFilter(), layout: FiltersLayout::AboveContent)
            ->columns([
                TextColumn::make('name')->label('Expense'),
                TextColumn::make('expenseCategory.name')->label('Category'),
                TextColumn::make('amount')->label('Amount')->getStateUsing(function ($record) {
                    return 'Tk '.number_format($record->amount, 2, '.', '');
                })
                    ->summarize(
                        Sum::make()->formatStateUsing(fn ($state) => number_format($state, 2, '.', '').' Tk')->label('Total')
                    ),
            ])
            ->filtersFormColumns(2)
            ->headerActions([
                ExportAction::make()
                    ->exporter(ExpenseReportExporter::class)
                    ->columnMapping(false)
                    ->modifyQueryUsing(function (Builder $query) {
                        if ($this->isFilter) {
                            $query->whereBetween('date', [$this->startDate, $this->endDate]);
                        }
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
                        $query->where('date', '>=', $data['startDateFilter']);
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
                        $query->where('date', '<=', $data['endDate']);
                    }
                }),

        ];
    }

    public function render()
    {
        return view('livewire.expense-report');
    }
}
