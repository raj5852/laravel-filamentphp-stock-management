<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class SupplierLedger extends Page
{
    protected static ?string $navigationIcon = 'fas-calculator';

    protected static string $view = 'filament.pages.supplier-ledger';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 15;
}
