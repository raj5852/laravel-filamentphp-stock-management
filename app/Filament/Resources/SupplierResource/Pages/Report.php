<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Filament\Resources\SupplierResource\Widgets\SupplierStats;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;

class Report extends Page
{
    use InteractsWithRecord;
    // use InteractsWithTable;

    protected static string $resource = SupplierResource::class;

    protected static string $view = 'filament.resources.supplier-resource.pages.report';

    // protected static ?string $title = $this->record->name;
    protected static ?string $title = '';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        static::$title = $this->record->supplier_name;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SupplierStats::make(['supplierId' => $this->record->id]),
        ];
    }
}
