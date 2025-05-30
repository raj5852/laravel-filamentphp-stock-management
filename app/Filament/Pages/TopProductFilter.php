<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class TopProductFilter extends Page
{
    protected static ?string $navigationIcon = 'fas-flask-vial';

    protected static string $view = 'filament.pages.top-product-filter';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $title = 'Top Selling Products';

    protected static ?string $navigationLabel = 'Top Product';
    protected static ?int $navigationSort = 10;
}
