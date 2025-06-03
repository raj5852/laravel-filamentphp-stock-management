<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class SupplierDueReport extends Page
{
    protected static ?string $navigationIcon = 'fas-handshake';

    protected static string $view = 'filament.pages.supplier-due-report';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 7;

    public static function canAccess(): bool
    {
        return auth()->user()->can('supplier due report');
    }
}
