<?php

namespace App\Providers;

use Filament\Actions\CreateAction;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
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

        FilamentView::registerRenderHook(
            PanelsRenderHook::TOPBAR_START,
            fn(): string => Blade::render('@livewire(\'top-left-message\')'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            fn(): string => Blade::render('@livewire(\'top-right-message\')'),
        );



        // URL::forceHttps();

    }
}
