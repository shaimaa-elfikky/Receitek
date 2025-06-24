<?php

namespace App\Providers\Filament;

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
use Filament\Navigation\NavigationGroup;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\FontProviders\GoogleFontProvider;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'danger' => '#CC0000',
                'gray' => '#A9A9A9',
                'info' => '#A7C7E7',
                'primary' => '#216CB9',
                'secondary' => '#FAF461',
                'success' => '#50C878',
                'warning' => '#FFAC1C',

            ])
            ->font('Changa', provider: GoogleFontProvider::class)
            ->favicon(asset('images/favicon.png'))
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('2rem')

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
            ])

            ->navigationGroups([
                NavigationGroup::make('Membership Management')
                    ->label('Membership Management'),
                NavigationGroup::make('Marketing Management')
                    ->label('Marketing Management'),
                NavigationGroup::make('Client Management')
                    ->label('Client Management'),
            ]);
    }
}
