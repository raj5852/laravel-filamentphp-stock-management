<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\Purchase;
use App\Models\Setting;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class PurchaseInvoice extends Page
{
    use InteractsWithRecord;

    protected static string $resource = PurchaseResource::class;

    protected static string $view = 'filament.resources.purchase-resource.pages.purchase-invoice';

    protected static ?string $title = 'Purchase Invoice';

    public function mount(int|string $record): void
    {

        $this->record = $this->resolveRecord($record);
    }

    protected function getViewData(): array
    {
        return [
            'setting' => Setting::query()->first(),
            'purchase' => Purchase::query()->with([
                'supplier:id,supplier_name,phone,address',
                'purchaseitems' => function ($query) {
                    $query->select('id', 'product_id', 'purchase_id', 'total_in_text', 'rate', 'total_rate')
                        ->with('product:id,product_name,product_code');
                },
            ])->find($this->record->id),

        ];
    }
}
