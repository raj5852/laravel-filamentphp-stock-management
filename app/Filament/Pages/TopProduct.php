<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Scopes\TenantScope;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class TopProduct extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'fas-square-up-right';

    protected static string $view = 'filament.pages.top-product';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $title = 'Top Selling Products(All Time)';

    protected static ?string $navigationLabel = 'Top Product - All Time';

    protected static ?int $navigationSort = 11;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::withoutGlobalScope(TenantScope::class)
                    ->join('product_details', 'products.id', '=', 'product_details.product_id')
                    ->select('products.*', 'product_details.sold_in_text')
                    ->where('products.tenant_id', auth()->user()->tenant_id)
                    ->orderBy('product_details.sold', 'desc')
            )
            ->columns([

                TextColumn::make('product_name'),
                TextColumn::make('product_code')->label('Code'),
                TextColumn::make('sold_in_text')
                    ->getStateUsing(function ($record) {
                        return new HtmlString('<span style="color:#33cabb" class="font-bold">'.$record->sold_in_text.'</span>');
                    })
                    ->label('Sold'),

            ])
            ->filters([
                //
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ])
            ->filtersFormColumns(4)

            ->paginated([50]);
    }
}
