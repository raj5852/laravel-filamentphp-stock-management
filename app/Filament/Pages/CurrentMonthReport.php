<?php

namespace App\Filament\Pages;

use App\Livewire\ReportOverview;
use Filament\Pages\Page;

class CurrentMonthReport extends Page
{
    protected static ?string $navigationIcon = 'fas-calendar-days';

    protected static string $view = 'filament.pages.current-month-report';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return auth()->user()->can('current month report');
    }


    protected function getHeaderWidgets(): array
    {
        return [
            ReportOverview::make([
                'startDate' => now()->startOfMonth(),
                'endDate' => now()->endOfMonth(),
            ]),
        ];
    }
}
