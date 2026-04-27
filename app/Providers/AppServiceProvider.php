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
    // Оставляем твой бренднейм и обсервер
    view()->share('brandname', config('app.name', 'Зверозор'));
    \App\Models\ReviewReceipt::observe(\App\Observers\ReviewReceiptObserver::class);

    \Illuminate\Support\Facades\View::composer('*', function ($view) {
        // --- ТВОЙ СУЩЕСТВУЮЩИЙ КОД ГОРОДА ---
        try {
            $cityId = session('selected_city_id') ?: request()->cookie('selected_city_id');
            
            $currentCity = null;
            if ($cityId) {
                $currentCity = \App\Models\City::find($cityId);
            }
            
            if (!$currentCity) {
                $currentCity = cache()->remember('default_city', 3600, function() {
                    return \App\Models\City::orderBy('name')->first();
                });
            }

            $view->with('currentCity', $currentCity);
            $currentCityName = $currentCity->name ?? 'вашем городе';
        } catch (\Exception $e) {
            $view->with('currentCity', null);
            $currentCityName = 'вашем городе';
        }

        // --- НОВАЯ ЛОГИКА SEO ---
        try {
            $seoManager = new \App\Services\SeoManager();
            
            // Ищем динамическую модель среди данных, переданных в view
            // Добавляем проверку всех твоих основных моделей
            $data = $view->getData();
            $model = $data['doctor'] 
                     ?? $data['clinic'] 
                     ?? $data['doctor'] // на случай разных имен переменных
                     ?? $data['animalDetail'] 
                     ?? null;

            // Генерируем мета-данные
            $seoMeta = $seoManager->getMeta($model);

            // Если в заголовке или описании есть плейсхолдер города, заменяем его
            // Это позволит в БД писать "Ветклиники в {city}", и оно подставит Краснодар
            $seoMeta['title'] = str_replace('{city}', $currentCityName, $seoMeta['title']);
            $seoMeta['description'] = str_replace('{city}', $currentCityName, $seoMeta['description']);

            $view->with('seoMeta', $seoMeta);
            
        } catch (\Exception $e) {
            // Если с SEO что-то пошло не так, даем дефолт, чтобы не уронить страницу
            $view->with('seoMeta', [
                'title' => config('app.name', 'Зверозор'),
                'description' => 'Честный рейтинг ветеринарных клиник и врачей.'
            ]);
        }
    });
}
}