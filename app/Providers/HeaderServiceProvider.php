<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;

class HeaderServiceProvider extends ServiceProvider
{
    public function register() {}

public function boot()
{
    View::composer('layouts.header', function ($view) {
        $route = Route::currentRouteName();
        $viewData = $view->getData();
        $path = request()->path(); // Получаем текущий путь

        $config = [
            'showSearch'     => true,
            'showHero'       => false,
            'isCompact'      => false,
            'isAdsPage'      => false, // Новый флаг для страницы объявлений
            'hideAddButtons' => false, // Флаг для скрытия кнопок добавления
            'title'          => 'Сайт про домашних животных',
            'description'    => 'На сайте вы сможете найти ветеринарные клиники...'
        ];

        // Логика для страницы /ads
        if ($path === 'ads') {
            $config['showSearch'] = false;
            $config['hideAddButtons'] = true;
            $config['isAdsPage'] = true;
            $config['isCompact'] = true; // Уменьшенный вид
        }

        // Ваша существующая логика
        if (request()->is('account*', 'legal*')) $config['showSearch'] = false;
        if ($route === 'clinics.show') $config['showSearch'] = true;
        if (request()->is('/')) $config['showHero'] = true;
        if (request()->is('clinics*', 'doctors*', 'account*')) $config['isCompact'] = true;

        // Страховка для городов
        if (!isset($viewData['cities'])) {
            $view->with('cities', \App\Models\City::all()); 
        }

        $view->with('h', (object)$config);
    });
}
}