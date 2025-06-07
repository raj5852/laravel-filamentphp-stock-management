<?php

namespace App\Providers\Filament;

use App\Filament\Helper\CustomLogin;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\AdvancedStatsOverviewWidget;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ApplyTenantThemeColors;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard as PagesDashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->maxContentWidth(MaxWidth::Full)
            ->navigationGroups([
                'Sale & Purchase',
                'Product Information',
                'Expenses & Payment',
                'Promotion',
                'Peoples',
                'Reports',
                'Setting & Customize',
            ])
            ->favicon('/images/favicon.ico')
            ->id('admin')
            ->path('user')
            // ->profile()
            ->login(CustomLogin::class)
            ->colors([
                'primary' => Color::Lime,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
                // PagesDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                // AdvancedStatsOverviewWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                AdminMiddleware::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                // ApplyTenantThemeColors::class,
            ])
            ->sidebarCollapsibleOnDesktop();
        // ->registration()
    }
}
