<?php

namespace App\Filament\Pages;

use App\Livewire\ReportOverview;
use Filament\Pages\Page;

class TodayReport extends Page
{
    protected static ?string $navigationIcon = 'fas-clock';

    protected static string $view = 'filament.pages.today-report';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()->can('today report');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ReportOverview::make([
                'startDate' => today(),
                'endDate' => today(),
            ]),
        ];
    }
}
