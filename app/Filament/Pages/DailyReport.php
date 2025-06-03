<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DailyReport extends Page
{
    protected static ?string $navigationIcon = 'fas-file-export';

    protected static string $view = 'filament.pages.daily-report';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 5;

    public static function canAccess(): bool
    {
        return auth()->user()->can('daily report');
    }
}
