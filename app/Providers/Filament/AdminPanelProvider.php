<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * The admin panel, themed in the brand palette.
 *
 * Magenta is primary because the fair track is the bulk of the work; cobalt marks
 * the conference track wherever the two appear side by side, exactly as on the
 * public site. Navigation is grouped the way the team actually works: the desk
 * (registrations), the programme, the content, the partners, the platform.
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->brandName('Next Step Admin')
            ->brandLogo(asset('assets/brand/nextstep-transparent-sm.png'))
            ->brandLogoHeight('2.2rem')
            ->favicon(asset('assets/brand/nextstep-transparent-sm.png'))
            ->colors([
                'primary' => Color::hex('#B64698'),   // Step Magenta — the fair track
                'info' => Color::hex('#2C4BE0'),      // Next Cobalt — the conference track
                'gray' => Color::Slate,
                'success' => Color::hex('#0E9B94'),
                'warning' => Color::hex('#F2A93B'),
                'danger' => Color::hex('#8A1B3C'),
            ])
            ->font('Manrope')
            ->maxContentWidth(Width::Full)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make()->label(fn () => __('admin.groups.registrations')),
                NavigationGroup::make()->label(fn () => __('admin.groups.messaging')),
                NavigationGroup::make()->label(fn () => __('admin.groups.programme')),
                NavigationGroup::make()->label(fn () => __('admin.groups.content')),
                NavigationGroup::make()->label(fn () => __('admin.groups.partners')),
                NavigationGroup::make()->label(fn () => __('admin.groups.platform')),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->pages([])
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
