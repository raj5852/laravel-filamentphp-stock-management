<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\OrderItem;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class Sell extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static ?string $title = '';

    protected static string $resource = ProductResource::class;

    protected static string $view = 'filament.resources.product-resource.pages.sell';

    public function mount(int|string $record): void
    {

        $this->record = $this->resolveRecord($record);
    }

    public function table(Table $table)
    {
        return $table
            ->query(OrderItem::where('product_id', $this->record->id)->select(['id', 'rate', 'total_rate', 'product_id', 'order_id', 'total_in_text'])->with('order:id,invoiceno,order_date', 'product:id,product_name'))
            ->heading(new HtmlString('<h2 style="font-size:23px; font-weight:bold">Sell History</h2>'))
            ->columns([
                TextColumn::make('order.order_date')
                    ->date()
                    ->label('Sell Date'),
                TextColumn::make('order.invoiceno')
                    ->getStateUsing(function ($record) {
                        return new HtmlString('<a href='.route('filament.admin.resources.sales.index', ['invoiceno' => $record->order->invoiceno]).' style="color:#33cabb">Pos#'.$record->order->invoiceno.'</a>');
                    })
                    ->label('Sale#'),
                TextColumn::make('product.product_name')
                    ->label('Name'),

                TextColumn::make('rate')
                    ->getStateUsing(fn ($record) => number_format($record->rate, 2))
                    ->label('Unit Price:'),
                TextColumn::make('total_in_text')
                    ->label('Quantity'),
                TextColumn::make('total_rate')
                    ->getStateUsing(fn ($record) => number_format($record->total_rate, 2))
                    ->label('Sub Total'),

            ])
            ->paginated([20, 50, 100]);
    }
}
