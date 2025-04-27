<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\CustomerResource\Widgets\CustomerOverview;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class report extends Page
{
    use InteractsWithRecord;

    protected static string $resource = CustomerResource::class;

    protected static string $view = 'filament.resources.customer-resource.pages.report';

    protected static ?string $title = '';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        static::$title = $this->record->customer_name;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerOverview::make(['customerId' => $this->record->id]),
        ];
    }
}
