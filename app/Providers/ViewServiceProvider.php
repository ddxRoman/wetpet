<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Делаем $currentCityName доступным во всех Blade-шаблонах
        View::composer('*', function ($view) {
            $currentCityName = auth()->user()?->city?->name ?? session('city_name', 'Ваш город');
            $view->with('currentCityName', $currentCityName);
        });
    }
}
