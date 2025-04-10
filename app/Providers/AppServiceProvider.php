<?php

namespace App\Providers;

use Filament\Actions\CreateAction;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Filament\Resources\Pages\CreateRecord::disableCreateAnother();
        \Filament\Actions\CreateAction::configureUsing(fn (CreateAction $action) => $action->createAnother(false));

    }
}
