<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ProfitLossReport extends Page
{
    protected static ?string $navigationIcon = 'fas-chart-line';

    protected static string $view = 'filament.pages.profit-loss-report';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()->can('profit loss report');
    }
}
