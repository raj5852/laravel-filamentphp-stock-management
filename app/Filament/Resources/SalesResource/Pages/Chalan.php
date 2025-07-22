<?php

namespace App\Filament\Resources\SalesResource\Pages;

use App\Filament\Resources\SalesResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class Chalan extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SalesResource::class;

    protected static string $view = 'filament.resources.sales-resource.pages.chalan';

    protected static ?string $title = '';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    protected function getViewData(): array
    {
        return [
            'setting' => Setting::query()->first(),
            'customer' => Customer::query()->withSum('orders', 'due')->find($this->record->customer_id),
            'order' => Order::query()->with('orderitems')->find($this->record->id),
        ];
    }
}
