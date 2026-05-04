<?php

namespace App\Providers\Filament;

use App\Domains\Auth\Filament\Pages\Login;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
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
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->brandName('Clínica Norte')
            ->colors([
                'primary' => Color::Teal,
            ])
            // Recursos del dominio Auth
            ->discoverResources(
                in: app_path('Domains/Auth/Filament/Resources'),
                for: 'App\\Domains\\Auth\\Filament\\Resources'
            )
            // Recursos del dominio Patients (Módulo 2)
            ->discoverResources(
                in: app_path('Domains/Patients/Filament/Resources'),
                for: 'App\\Domains\\Patients\\Filament\\Resources'
            )
            // Recursos del dominio Orders (Módulo 3)
            ->discoverResources(
                in: app_path('Domains/Orders/Filament/Resources'),
                for: 'App\\Domains\\Orders\\Filament\\Resources'
            )
            // Recursos del dominio Samples (Módulo 4)
            ->discoverResources(
                in: app_path('Domains/Samples/Filament/Resources'),
                for: 'App\\Domains\\Samples\\Filament\\Resources'
            )
            // Recursos del dominio Imaging (Módulo 5)
            ->discoverResources(
                in: app_path('Domains/Imaging/Filament/Resources'),
                for: 'App\\Domains\\Imaging\\Filament\\Resources'
            )
            // Recursos del dominio Catalog
            ->discoverResources(
                in: app_path('Domains/Catalog/Filament/Resources'),
                for: 'App\\Domains\\Catalog\\Filament\\Resources'
            )
            // Recursos generales (para módulos futuros)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
