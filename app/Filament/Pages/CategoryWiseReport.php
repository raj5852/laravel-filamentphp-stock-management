<?php

namespace App\Filament\Pages;

use App\Models\Brand;
use App\Models\Category;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class CategoryWiseReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $navigationIcon = 'fas-object-group';

    protected static string $view = 'filament.pages.category-wise-report';

    protected static ?string $title = 'Category-wise Sales and Purchases Report';

    protected static ?string $navigationLabel = 'Category Wise  Report';

    protected static ?int $navigationSort = 12;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                function () {
                    $dateForm = null;
                    $dateEnd = null;
                    if (! empty($this->tableFilters['date']['date_from'])) {
                        $dateForm = $this->tableFilters['date']['date_from'];
                    }
                    if (! empty($this->tableFilters['date_end']['date_end'])) {
                        $dateEnd = $this->tableFilters['date_end']['date_end'];
                    }

                    return Category::query()

                        // Total Sales Quantity
                        ->withSum(['orderItems as total_sales_quantity' => function ($query) use ($dateForm, $dateEnd) {
                            if (! empty($dateForm) && ! empty($dateEnd)) {
                                $query->whereHas('order', function ($query) use ($dateForm, $dateEnd) {
                                    $query->whereBetween('order_date', [$dateForm, $dateEnd]);
                                });
                            }
                        }], 'total_qty')

                        // purchase quantity
                        ->withSum(['purchaseItems as purchase_quantity' => function ($query) use ($dateForm, $dateEnd) {
                            if (! empty($dateForm) && ! empty($dateEnd)) {
                                $query->whereHas('purchase', function ($query) use ($dateForm, $dateEnd) {
                                    $query->whereBetween('purchase_date', [$dateForm, $dateEnd]);
                                });
                            }
                        }], 'total_qty')

                        // Total Sales Amount
                        ->withSum(['orderItems as total_sales_amount' => function ($query) use ($dateForm, $dateEnd) {
                            if (! empty($dateForm) && ! empty($dateEnd)) {
                                $query->whereHas('order', function ($query) use ($dateForm, $dateEnd) {
                                    $query->whereBetween('order_date', [$dateForm, $dateEnd]);
                                });
                            }
                        }], 'total_rate')

                        // Total Purchase Amount
                        ->withSum(['purchaseItems as purchase_amount' => function ($query) use ($dateForm, $dateEnd) {
                            if (! empty($dateForm) && ! empty($dateEnd)) {
                                $query->whereHas('purchase', function ($query) use ($dateForm, $dateEnd) {
                                    $query->whereBetween('purchase_date', [$dateForm, $dateEnd]);
                                });
                            }
                        }], 'total_rate');
                }

            )
            ->columns([

                TextColumn::make('name')->label('Category Name'),
                TextColumn::make('total_sales_quantity')
                    ->getStateUsing(fn ($record) => $record->total_sales_quantity ?: 0)
                    ->label('Total Sales Quantity'),

                TextColumn::make('purchase_quantity')
                    ->getStateUsing(fn ($record) => ($record->purchase_quantity ?: 0))
                    ->label('Total Purchase Quantity'),

                TextColumn::make('total_sales_amount')->getStateUsing(fn ($record) => number_format($record->total_sales_amount, 2).' Tk')->label('Total Sales Amount'),
                TextColumn::make('purchase_amount')->getStateUsing(fn ($record) => number_format(($record->purchase_amount ?: 0), 2).' Tk')->label('Total Purchase Amount'),
                TextColumn::make('id')->label('Profit')->getStateUsing(fn ($record) => number_format($record->total_sales_amount - (($record->purchase_amount ?: 0)), 2)),

            ])
            ->filters([
                Filter::make('id')
                    ->label('')
                    ->form([
                        Select::make('category_id')
                            ->label('Category')
                            ->options(Category::query()->pluck('name', 'id'))
                            ->placeholder('Select Category')
                            ->searchable(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['category_id'],
                            fn ($query, $term) => $query->where('id', $term)
                        );
                    }),
                Filter::make('brand_id')
                    ->label('')
                    ->form([
                        Select::make('brand_id')
                            ->label('Brand')
                            ->options(Brand::query()->pluck('brand_name', 'id'))
                            ->placeholder('Select Brand')
                            ->searchable(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['brand_id'],
                            fn ($query, $term) => $query->whereHas(
                                'products',
                                fn ($query) => $query->where('brand_id', $term)
                            )
                        );
                    }),
                Filter::make('date')
                    ->label('')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('Start Date')
                            ->native(false)
                            ->placeholder('Start Date'),
                    ]),

                Filter::make('date_end')
                    ->label('')
                    ->form([
                        DatePicker::make('date_end')
                            ->label('End Date')
                            ->native(false)
                            ->placeholder('End Date'),
                    ]),

            ], layout: FiltersLayout::AboveContent)
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ])
            ->filtersFormColumns(4)

            ->paginated([10, 25, 50, 100]);
    }
}
