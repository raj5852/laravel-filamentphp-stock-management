<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class TopCustomer extends Page
{
    protected static ?string $navigationIcon = 'fas-web-awesome';

    protected static string $view = 'filament.pages.top-customer';

    protected static ?string $navigationGroup = 'Reports';
}
