<?php

namespace App\Providers;

use Filament\Actions\CreateAction;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\URL;
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

        FilamentAsset::register([
            Js::make('example-local-script', asset('js/custom-filament.js')),
        ]);

        URL::forceHttps();
    }
}
