<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
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
        // Оставляем бренднейм и обсервер
        view()->share('brandname', config('app.name', 'Зверозор'));
        \App\Models\ReviewReceipt::observe(\App\Observers\ReviewReceiptObserver::class);
Paginator::useBootstrapFive();
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            // --- ЛОГИКА ОПРЕДЕЛЕНИЯ ГОРОДА ---
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

            // --- ЛОГИКА SEO (С ПРОВЕРКОЙ НА ПЕРЕОПРЕДЕЛЕНИЕ) ---
            try {
                $data = $view->getData();

                // Если контроллер уже передал seoMeta (как в твоем showBreedPage), 
                // то провайдер просто пропускает этот блок и не затирает данные.
                if (!isset($data['seoMeta'])) {
                    
                    $seoManager = new \App\Services\SeoManager();
                    
                    // Ищем модель для автоматической генерации мета-тегов
$model = $data['animal']->details // Пытаемся взять детали из объекта животного
         ?? $data['animal'] 
         ?? $data['doctor'] 
         ?? $data['clinic'] 
         ?? null;

                    $seoMeta = $seoManager->getMeta($model);

                    // Динамическая замена города в шаблонах
                    $seoMeta['title'] = str_replace('{city}', $currentCityName, $seoMeta['title']);
                    $seoMeta['description'] = str_replace('{city}', $currentCityName, $seoMeta['description']);

                    $view->with('seoMeta', $seoMeta);
                }
                
            } catch (\Exception $e) {
                // Если возникла ошибка и SEO всё еще не установлено — даем дефолт
                if (!isset($view->getData()['seoMeta'])) {
                    $view->with('seoMeta', [
                        'title' => config('app.name', 'Зверозор'),
                        'description' => 'Честный рейтинг ветеринарных клиник и врачей.'
                    ]);
                }
            }
        });
    }
}