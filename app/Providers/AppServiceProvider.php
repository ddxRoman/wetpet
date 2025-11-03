<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\City;
use Illuminate\Support\Facades\View;

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
view()->share('brandname', env('BRAND_NAME', 'Зверозор'));



    View::composer('*', function ($view) {
        $cityId = session('selected_city_id') ?: request()->cookie('selected_city_id') ?: null;
        
        // Если нет, можно подставить дефолт (например первый город)
        $currentCity = null;
        if ($cityId) {
            $currentCity = City::find($cityId);
        }
        if (!$currentCity) {
            $currentCity = City::orderBy('name')->first();
        }

        $view->with('currentCity', $currentCity);
    });
}
}