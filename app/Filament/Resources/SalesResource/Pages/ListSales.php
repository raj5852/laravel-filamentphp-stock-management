<?php

namespace App\Filament\Resources\SalesResource\Pages;

use App\Filament\Resources\SalesResource;
use App\Livewire\SalesOverview;
use Filament\Resources\Pages\ListRecords;

class ListSales extends ListRecords
{
    protected static string $resource = SalesResource::class;

    protected function getHeaderWidgets(): array
    {
        $dinamicTime = today();

        return [
            SalesOverview::make(['date' => $dinamicTime]),
        ];
    }
}
