<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
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
    return response()->json(City::select('id', 'name', 'slug')->get());
}


public function add(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'country' => 'required|string|max:255',
        'region' => 'required|string|max:255',
    ]);

    $slug = \Str::slug($validated['name']);
    $count = City::where('slug', 'like', $slug . '%')->count();
    if ($count > 0) {
        $slug .= '-' . ($count + 1);
    }

    $city = City::create([
        'name' => $validated['name'],
        'slug' => $slug,
        'country' => $validated['country'],
        'region' => $validated['region'],
        'verified' => 'unconfirmed',
        'user_id' => Auth::id(), // 👈 сохраняем ID автора
    ]);

    return response()->json(['success' => true, 'city' => $city]);
}

// в App\Http\Controllers\CityController
public function citiesByRegion($region)
{
    // лог для отладки (убери в продакшн)
    \Log::info('citiesByRegion called with region: ' . $region);
$regions = \App\Models\City::select('region')->distinct()->orderBy('region')->get();

    // безопасное сравнение: trim + lower
    $cities = \App\Models\City::whereRaw(
        'LOWER(TRIM(region)) = LOWER(TRIM(?))',
        [$region]
    )->orderBy('name')->get(['id','name']);

    \Log::info('citiesByRegion result count: ' . $cities->count());

    return response()->json($cities);
}



public function getCities()
{
    $user = Auth::user();

    // Базовый запрос — только подтверждённые города
    $query = City::where('verified', 'confirmed');

    // Если пользователь авторизован, добавляем и его непроверенные города
    if ($user) {
        $query->orWhere(function($q) use ($user) {
            $q->where('verified', 'unconfirmed')
              ->where('user_id', $user->id);
        });
    }

    $cities = $query->orderBy('name')->get();

    return response()->json($cities);
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
   public function index()
{
    $user = Auth::user();

    // Базовый запрос — только подтверждённые города
    $query = City::where('verified', 'confirmed');

    // Если пользователь авторизован — добавляем его собственные города
    if ($user) {
        $query->orWhere(function ($q) use ($user) {
            $q->where('verified', 'unconfirmed')
              ->where('user_id', $user->id);
        });
    }

    $cities = $query->orderBy('name')->get(['id', 'name', 'large_city']);

    return response()->json($cities);
}
    /**
     * Устанавливает выбранный город
     */
    public function set(Request $request)
    {
        logger()->info('CityController@set called', [
    'request' => $request->all(),
    'session_before' => session()->all(),
]);

        $request->validate([
            'city_id' => 'required|exists:cities,id',
        ]);

        $city = City::findOrFail($request->city_id);

        // Сохраняем в сессию
        session([
            'city_id' => $city->id,
            'city_name' => $city->name,
        ]);
        logger()->info('CityController@set session after', session()->all());


        // Если пользователь авторизован — обновляем поле city_id в users
        if (auth()->check()) {
            $user = auth()->user();
            $user->city_id = $city->id;
            $user->save();
        }

        return response()->json(['city' => $city]);
    }
}
