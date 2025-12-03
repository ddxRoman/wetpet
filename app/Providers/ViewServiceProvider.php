<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\City;

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
        // 🔹 1. Передаём текущий выбранный город
        View::composer('*', function ($view) {
            $currentCityName = auth()->user()?->city?->name ?? session('city_name', 'Выберите город');
            $view->with('currentCityName', $currentCityName);
        });

        // 🔹 2. Передаём все города для селектов
        View::composer('*', function ($view) {
            $cities = City::orderBy('name')->get();

            $view->with('cities', City::orderBy('name')->get());
            
        });
        
    }
}
