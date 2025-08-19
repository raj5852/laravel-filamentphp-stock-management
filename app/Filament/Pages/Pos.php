<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Pos extends Page
{
    protected static ?string $navigationIcon = 'fas-cart-shopping';

    // protected static string $view = 'filament.pages.pos';

    protected static ?string $title = '';

    protected static ?string $navigationLabel = 'POS';

    protected static ?string $navigationGroup = 'Sale & Purchase';

    public static function canAccess(): bool
    {
        return auth()->user()->can('pos');
    }


    public function getView(): string
    {
        return 'filament.pages.pos';   
    }
}