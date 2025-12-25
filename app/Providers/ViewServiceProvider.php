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
        View::composer('*', function ($view) {

            $data = $view->getData();

            /**
             * 🧭 1. Город, выбранный пользователем (как есть)
             */
            $currentCityName =
                auth()->user()?->city?->name
                ?? session('city_name')
                ?? 'Выберите город';

            /**
             * 🏥 2. Город конкретной страницы
             */
            $pageCityName = null;

            // 👉 Страница врача
            if (isset($data['doctor'])) {

                // doctors.city_id → cities.name
                if ($data['doctor']->relationLoaded('city') && $data['doctor']->city) {
                    $pageCityName = $data['doctor']->city->name;
                }
            }

            // 👉 Страница клиники
            if (!$pageCityName && isset($data['clinic'])) {

                // clinics.city (строка)
                $pageCityName = $data['clinic']->city;
            }

            $view->with([
                'currentCityName' => $currentCityName,
                'pageCityName'     => $pageCityName,
            ]);
        });

        /**
         * 🔹 Все города (селекты)
         */
        View::composer('*', function ($view) {
            $view->with('cities', City::orderBy('name')->get());
        });
    }
}
