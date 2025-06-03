<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CustomerDueReport extends Page
{
    protected static ?string $navigationIcon = 'fas-tent-arrow-left-right';

    protected static string $view = 'filament.pages.customer-due-report';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 6;

    public static function canAccess(): bool
    {
        return auth()->user()->can('customer due report');
    }
}
