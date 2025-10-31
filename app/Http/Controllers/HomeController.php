<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
public function index()
{
    // Получаем название города из сессии (или подставляем значение по умолчанию)
    $currentCityName = session('city_name', 'Ваш город');

    // Передаём переменную в Blade
    return view('welcome', compact('currentCityName'));
}

}
