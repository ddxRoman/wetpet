<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;

class CityController extends Controller
{
    /**
     * Возвращает список городов (для поиска)
     */
    public function index(Request $request)
    {
        $q = $request->get('q', '');

        $cities = City::query()
            ->when($q, fn($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name']);

        return response()->json($cities);
    }

    /**
     * Устанавливает выбранный город
     */
    public function set(Request $request)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
        ]);

        $city = City::findOrFail($request->city_id);

        // Сохраняем в сессию
        session([
            'city_id' => $city->id,
            'city_name' => $city->name,
        ]);

        // Если пользователь авторизован — обновляем поле city_id в users
        if (auth()->check()) {
            $user = auth()->user();
            $user->city_id = $city->id;
            $user->save();
        }

        return response()->json(['city' => $city]);
    }
}
