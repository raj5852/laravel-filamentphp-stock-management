<?php

namespace App\Livewire;

use App\Filament\Exports\TopSaleProductExporter;
use App\Models\Product;
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

class TopSaleProduct extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $startDate;

    public $endDate;

    public $isFilter = true;

    public $defaultFilter = false;

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
                function () {

                    if (! empty($this->tableFilters['start_date']['start_date'])) {
                        $this->startDate = $this->tableFilters['start_date']['start_date'];
                        $this->defaultFilter = true;
                    }
                    if (! empty($this->tableFilters['end_date']['end_date'])) {
                        $this->endDate = $this->tableFilters['end_date']['end_date'];
                        $this->defaultFilter = true;
                    }

                    return Product::query()
                        ->withSum(['orderitems as quantity' => function ($query) {
                            if ($this->isFilter || $this->defaultFilter) {
                                $query->whereHas('order', function ($query) {
                                    $query->whereBetween('order_date', [$this->startDate, $this->endDate]);
                                });
                            }
                        }], 'total_qty')
                        ->withCount(['orderitems as total_sale' => function ($query) {
                            if ($this->isFilter || $this->defaultFilter) {
                                $query->whereHas('order', function ($query) {
                                    $query->whereBetween('order_date', [$this->startDate, $this->endDate]);
                                });
                            }
                        }])
                        ->withSum(['orderitems as sale_amount' => function ($query) {
                            if ($this->isFilter || $this->defaultFilter) {
                                $query->whereHas('order', function ($query) {
                                    $query->whereBetween('order_date', [$this->startDate, $this->endDate]);
                                });
                            }
                        }], 'total_rate')
                        ->orderBy('quantity', 'desc')
                        ->having('quantity', '>', 0);
                }

            )
            ->heading(new HtmlString('<h2 style="font-size:23px; font-weight:bold">Top Sale Product</h2>'))
            ->filters($this->setFilter(), layout: FiltersLayout::AboveContent)
            ->columns([
                TextColumn::make('product_name')->label('Product Name')->wrap(),
                TextColumn::make('quantity')->label('Quantity')
                    ->summarize(
                        Sum::make()->formatStateUsing(fn($state) => $state)->label('Qty')
                    ),
                TextColumn::make('total_sale')->label('Total Sale')
                    ->summarize(
                        Sum::make()->formatStateUsing(fn($state) => $state)->label('Total')
                    ),
                TextColumn::make('sale_amount')->label('Sale Amount')->getStateUsing(function ($record) {
                    return 'TK ' . number_format($record->sale_amount, 2, '.', '');
                })
                    ->summarize(
                        Sum::make()->formatStateUsing(fn($state) => number_format($state, 2, '.', '') . ' TK')->label('Total')
                    ),

            ])
            ->filtersFormColumns(2)
            ->headerActions([
                ExportAction::make()
                    ->exporter(TopSaleProductExporter::class)
                    ->columnMapping(false)
                    ->modifyQueryUsing(function (Builder $query) {
                        // Don't add the same columns again, just apply the filters
                        if ($this->isFilter || $this->defaultFilter) {

                            $query->whereHas('orderitems', function ($subQuery) {
                                $subQuery->whereHas('order', function ($orderQuery) {
                                    $orderQuery->whereBetween('order_date', [$this->startDate, $this->endDate]);
                                });
                            })
                                ->having('quantity', '>', 0);
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

        ];
    }

    public function render()
    {
        return view('livewire.top-sale-product');
    }
}
