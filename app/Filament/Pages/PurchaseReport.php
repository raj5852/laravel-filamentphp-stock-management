<?php

namespace App\Filament\Pages;

use App\Models\Product as ModelsProduct;
use App\Models\PurchaseItem;
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
use Illuminate\Support\HtmlString;

class PurchaseReport extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static string $view = 'filament.pages.purchase-report';
    protected static ?int $navigationSort = 13;

    public function table(Table $table): Table
    {
        return $table
            ->query(PurchaseItem::query()->with(['purchase:id,purchase_date,billno']))
            ->columns([
                TextColumn::make('purchase.purchase_date')
                    ->label('Date'),
                TextColumn::make('purchase.billno')
                    ->label('Purchase No')
                    ->getStateUsing(function ($record) {
                        return new HtmlString("<div><a href='" . route('filament.admin.resources.purchases.index', ['billno' => $record->purchase->billno]) . "' class='text-indigo-500 hover:underline'>Purchase#{$record->purchase->billno}</a></div>");
                    })
                    ->html(),

                TextColumn::make('product.product_name')
                    ->label('Product Name'),

                TextColumn::make('total_in_text')
                    ->label('Quantity'),

                TextColumn::make('rate')
                    ->getStateUsing(fn($record) => number_format($record->rate, 2) . ' Tk')
                    ->label('Unit Price'),
                TextColumn::make('total_rate')
                    ->getStateUsing(fn($record) => number_format($record->total_rate, 2) . ' Tk')
                    ->label('Subtotal'),

            ])
            ->filters([
                Filter::make('product_id')
                    ->label('')
                    ->form([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(ModelsProduct::query()->pluck('product_name', 'id'))
                            ->placeholder('Select Product')
                            ->searchable(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['product_id'],
                            fn($query, $term) => $query->where('product_id', $term)
                        );
                    }),
                Filter::make('start_date')
                    ->label('')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->native(false)
                            ->placeholder('Start Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['start_date'],
                            fn($query, $term) => $query->whereHas('purchase', fn($query) => $query->where('purchase_date', '>=', $term))
                        );
                    }),
                Filter::make('end_date')
                    ->label('')
                    ->form([
                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->native(false)
                            ->placeholder('End Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['end_date'],
                            fn($query, $term) => $query->whereHas('purchase', fn($query) => $query->where('purchase_date', '<=', $term))
                        );
                    }),

            ], layout: FiltersLayout::AboveContent)
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ])
            ->filtersFormColumns(3)

            ->paginated([10, 25, 50, 100]);
    }
}
