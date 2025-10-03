<?php

namespace App\Filament\Resources\SalesResource\Pages;

use App\Filament\Resources\SalesResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class PosReceipt extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SalesResource::class;

    // Remove the static view property
    // protected static string $view = 'filament.resources.sales-resource.pages.pos-receipt';

    protected static ?string $title = '';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    // Add this method to dynamically determine the view
    public function getView(): string
    {
        // Default view
        $setting = Setting::first();

        // return 'filament.resources.sales-resource.pages.pos-receipt';
        if ($setting->invoice_design == 'a4') {
            return 'filament.resources.sales-resource.pages.pos-receipt';
        } else {

            return 'filament.resources.sales-resource.pages.80mm';
        }
    }

    protected function getViewData(): array
    {
        return [
            'setting' => Setting::query()->first(),
            'customer' => Customer::query()->withSum('orders', 'due')->find($this->record->customer_id),
            'order' => Order::query()->with('orderitems', 'returnlist')->find($this->record->id),
        ];
    }
}
