<?php

namespace App\Filament\Resources\SalesResource\Pages;

use App\Filament\Resources\SalesResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class PosReceipt extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SalesResource::class;

    protected static string $view = 'filament.resources.sales-resource.pages.pos-receipt';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    protected function getViewData(): array
    {
        return [

        ];
    }
}
