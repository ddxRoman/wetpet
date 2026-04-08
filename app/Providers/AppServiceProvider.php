<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\City;
use Illuminate\Support\Facades\View;
use App\Models\ReviewReceipt;
use App\Observers\ReviewReceiptObserver;

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
    // Вместо env() лучше использовать config()
    view()->share('brandname', config('app.name', 'Зверозор'));

    ReviewReceipt::observe(ReviewReceiptObserver::class);

    View::composer('*', function ($view) {
        // Оборачиваем в try-catch, чтобы из-за одного города не падал весь сайт
        try {
            $cityId = session('selected_city_id') ?: request()->cookie('selected_city_id');
            
            $currentCity = null;
            if ($cityId) {
                $currentCity = City::find($cityId);
            }
            
            if (!$currentCity) {
                // Используем кэш, чтобы не мучить базу при каждом обновлении страницы
                $currentCity = cache()->remember('default_city', 3600, function() {
                    return City::orderBy('name')->first();
                });
            }

            $view->with('currentCity', $currentCity);
        } catch (\Exception $e) {
            // Если база лежит, сайт хотя бы откроется без города
            $view->with('currentCity', null);
        }
    });
}
}