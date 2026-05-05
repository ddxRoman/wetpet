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
    // Используем '*', чтобы переменная была доступна везде, 
    // где вызывается header.blade.php
    View::composer('*', function ($view) {
        
        // Если переменная 'h' уже передана (например, из контроллера), 
        // или если это не основной макет, ничего не делаем, 
        // но дефолтное значение все равно подготовим.
        $config = [
            'showSearch'     => true,
            'showHero'       => false,
            'isCompact'      => false,
            'isAdsPage'      => false,
            'hideAddButtons' => false,
            'title'          => 'Сайт про домашних животных',
            'description'    => 'На сайте вы сможете найти ветеринарные клиники...'
        ];

        $route = Route::currentRouteName();
        
        // Логика условий
        if (request()->is('ads*')) {
            $config['showSearch'] = false;
            $config['hideAddButtons'] = true;
            $config['isAdsPage'] = true;
            $config['isCompact'] = true;
        }

        if (request()->is('account*', 'legal*')) $config['showSearch'] = false;
        if (request()->is('clinics*', 'doctors*', 'account*')) $config['isCompact'] = true;
        if ($route === 'clinics.show') $config['showSearch'] = true;
        if (request()->is('/')) $config['showHero'] = true;

        // Передаем города, только если их нет
        if (!isset($view->getData()['cities'])) {
            $view->with('cities', \Illuminate\Support\Facades\Cache::remember('all_cities', 3600, function() {
                return \App\Models\City::all();
            }));
        }

        // ПРИНУДИТЕЛЬНО передаем объект h
        $view->with('h', (object)$config);
    });
}
}