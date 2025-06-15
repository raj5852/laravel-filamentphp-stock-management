<?php

namespace App\Livewire;

use App\Filament\Exports\TopCustomerExporter;
use App\Models\Customer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class TopCustomer extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                function () {
                    $startOfMonth = null;
                    $endOfMonth = null;

                    if (! empty($this->tableFilters['start_date']['start_date'])) {
                        $startOfMonth = $this->tableFilters['start_date']['start_date'];
                    }
                    if (! empty($this->tableFilters['end_date']['end_date'])) {
                        $endOfMonth = $this->tableFilters['end_date']['end_date'];
                    }

                    return Customer::query()
                        ->where('is_default', '!=', 1)
                        ->withSum(['orders as total_sell' => function ($query) use ($startOfMonth, $endOfMonth) {
                            $query->when($startOfMonth, function ($subQ) use ($startOfMonth) {
                                $subQ->where('order_date', '>=', $startOfMonth);
                            })
                                ->when($endOfMonth, function ($subQ) use ($endOfMonth) {
                                    $subQ->where('order_date', '<=', $endOfMonth);
                                });
                        }], 'receivable')
                        ->orderBy('total_sell', 'desc');
                }
            )
            ->heading(new HtmlString('<h2 style="font-size:23px; font-weight:bold">Top Customers (Based on Sell Amount)</h2>'))
            // ->description(new HtmlString('<p class="mt-1 max-w-2xl text-sm text-gray-600 dark:text-gray-300">Report From ' . Carbon::parse($this->start_date)->format('Y-m-d') . ' to ' . Carbon::parse($this->end_date)->format('Y-m-d') . '</p>'))
            ->filters([
                Filter::make('start_date')
                    ->label('')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('')
                            ->placeholder('Select Start Date')
                            ->native(false),
                    ]),
                Filter::make('end_date')
                    ->label('')
                    ->form([
                        DatePicker::make('end_date')
                            ->label('')
                            ->placeholder('Select End Date')
                            ->native(false),
                    ]),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->columns([
                TextColumn::make('customer_name')->label('Name'),
                TextColumn::make('email')->label('Email'),
                TextColumn::make('phone')->label('Phone'),
                TextColumn::make('address')->label('Address')->wrap(),
                TextColumn::make('total_sell')->label('Total Sell')
                    ->default(0)
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' TK'),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export')
                    ->icon('fas-download')
                    ->columnMapping(false)
                    ->modalHeading('Export Top Customers (Based on Sell Amount)')
                    ->exporter(TopCustomerExporter::class)
                    ->modifyQueryUsing(function ($query) {
                        $startOfMonth = null;
                        $endOfMonth = null;

                        if (! empty($this->tableFilters['start_date']['start_date'])) {
                            $startOfMonth = $this->tableFilters['start_date']['start_date'];
                        }
                        if (! empty($this->tableFilters['end_date']['end_date'])) {
                            $endOfMonth = $this->tableFilters['end_date']['end_date'];
                        }

                        return $query->where('is_default', '!=', 1)
                            ->withSum(['orders as total_sell' => function ($query) use ($startOfMonth, $endOfMonth) {
                                $query->when($startOfMonth, function ($subQ) use ($startOfMonth) {
                                    $subQ->where('order_date', '>=', $startOfMonth);
                                })
                                    ->when($endOfMonth, function ($subQ) use ($endOfMonth) {
                                        $subQ->where('order_date', '<=', $endOfMonth);
                                    });
                            }], 'receivable')
                            ->orderBy('total_sell', 'desc');
                    }),

            ])
            ->paginated([10, 25, 50, 100]);
    }

    public function render()
    {
        return view('livewire.top-customer');
    }
}
