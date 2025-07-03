<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

use App\Filament\Exports\StockExporter;
use App\Models\Product;
use App\Models\PurchaseItem;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ExpirdList extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;
    protected static ?string $navigationIcon = 'fas-circle-exclamation';

    protected static string $view = 'filament.pages.expird-list';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = -1;

    public static function canAccess(): bool
    {
        return auth()->user()->can('expird list');
    }

    public function table(Table $table): Table
    {
        $today = today();
        return $table
            ->query(
                PurchaseItem::query()
                    ->with('product.unit', 'product.subunit', 'purchase.supplier:id,supplier_name')
                    ->where('expiry_date', '<', $today)
                    ->where('available_qty', '>', 0)
                    ->latest()
            )
            ->columns([
                TextColumn::make('product.product_name')
                    ->label('Product Name'),
                TextColumn::make('purchase.supplier.supplier_name')
                    ->label('Supplier'),
                TextColumn::make('expiry_date')
                    ->date()
                    ->label('Expiry Date'),
                TextColumn::make('available_qty')
                    ->getStateUsing(function ($record) {
                        return getTotalStockInTextWithoutModal($record->product, $record->available_qty);
                    })
                    ->label('Expired Quantity'),

            ])
            ->paginated([20, 50, 100])
        ;
    }
}
