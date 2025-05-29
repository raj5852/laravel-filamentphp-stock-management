<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class DailyReport extends Page
{
    protected static ?string $navigationIcon = 'fas-file-export';

    protected static string $view = 'filament.pages.daily-report';

    protected static ?string $navigationGroup = 'Reports';
}
