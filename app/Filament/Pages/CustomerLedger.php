<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CustomerLedger extends Page
{
    protected static ?string $navigationIcon = 'fas-book-open';

    protected static string $view = 'filament.pages.customer-ledger';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 14;

    public static function canAccess(): bool
    {
        return auth()->user()->can('customer ledger');
    }
}
