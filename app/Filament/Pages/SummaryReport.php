<?php

namespace App\Filament\Pages;

use App\Livewire\ReportOverview;
use Filament\Pages\Page;

class SummaryReport extends Page
{
    protected static ?string $navigationIcon = 'fas-table-list';

    protected static string $view = 'filament.pages.summary-report';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return auth()->user()->can('summary report');
    }


    protected function getHeaderWidgets(): array
    {
        return [
            ReportOverview::make([
                'isFilter' => false,
            ]),
        ];
    }
}
