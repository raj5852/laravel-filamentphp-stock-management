<?php

namespace App\Filament\Pages;

use App\Models\PurchaseItem;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

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
            ->emptyStateHeading('No expired products found')
            ->emptyStateDescription('All expired products will be listed here.')
            ->emptyStateIcon('heroicon-o-exclamation-triangle')
            ->paginated([20, 50, 100]);
    }
}
