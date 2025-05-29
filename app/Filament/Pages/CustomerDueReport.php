<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CustomerDueReport extends Page
{
    protected static ?string $navigationIcon = 'fas-tent-arrow-left-right';

    protected static string $view = 'filament.pages.customer-due-report';

    protected static ?string $navigationGroup = 'Reports';
}
