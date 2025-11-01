<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;

class CityController extends Controller
{
    public function getAll()
{
    return response()->json(\App\Models\City::orderBy('name')->get(['id', 'name']));
}


public function all()
{
    return response()->json(\App\Models\City::select('id', 'name')->orderBy('name')->get());
}

public function add(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'country' => 'required|string|max:255',
        'region' => 'required|string|max:255',
    ]);

    $city = \App\Models\City::create($validated);

    return response()->json(['success' => true, 'city' => $city]);
}


     public function search(Request $request)
    {
        $query = $request->get('query', '');
        $cities = City::where('name', 'like', "%{$query}%")
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($cities);
    }

    // Добавление нового города (с отметкой unconfirmed)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'country' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'name' => 'required|string|max:255|unique:cities,name',
        ]);

        $city = City::create([
            'country' => $validated['country'],
            'region' => $validated['region'],
            'name' => $validated['name'],
            'verified' => 'unconfirmed',
        ]);

        return response()->json([
            'success' => true,
            'city' => $city
        ]);
    }


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
