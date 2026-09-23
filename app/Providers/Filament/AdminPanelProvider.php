<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsureUserIsAdmin;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        // Подключаем кастомную тему админки (см. public/css/filament/admin/custom-theme.css).
        // Регистрируем по URL — файл лежит в /public напрямую, поэтому пересборка
        // ассетов (npm run build / php artisan filament:assets) не требуется.
        FilamentAsset::register([
            Css::make('zverozor-admin-theme', asset('css/filament/admin/custom-theme.css')),
        ]);
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('web')
            ->brandName(config('app.name', 'Зверозор'))
            ->brandLogo(fn () => \Illuminate\Support\Facades\Storage::url('logo/logo1.png'))
            ->brandLogoHeight('2.2rem')
            ->favicon(asset('favicon.ico'))
            ->font('Rubik')
            // Админка всегда светлая — не зависит от системной/браузерной тёмной темы
            // ->darkMode(false)
            ->colors([
                // Фирменный сине-бирюзовый акцент сайта (кнопки, ссылки, активные пункты меню)
                'primary' => Color::hex('#1ccfc9'),
                // Холодный сине-серый вместо стандартного нейтрального серого —
                // ближе к светлым сине-белым фонам сайта (#f8faff / #eef3ff)
                'gray' => Color::Slate,
            ])
            // Оставляем только основной путь, он сам просканирует подпапки
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
                EnsureUserIsAdmin::class,
            ]);
    }
}