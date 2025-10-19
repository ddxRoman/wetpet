<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CityController extends Controller
{
    /**
     * Возвращает список городов (с поддержкой поиска)
     */
    public function index(Request $request)
    {
        $q = $request->query('q');

        $cities = City::query()
            ->when($q, fn($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'slug']); // Только нужные поля

        return response()->json($cities);
    }

    /**
     * Устанавливает выбранный город пользователю
     */
    public function setCity(Request $request)
    {
        try {
            $request->validate([
                'city_id' => 'required|integer|exists:cities,id',
            ]);

            $city = City::find($request->city_id);

            if (!$city) {
                return response()->json(['error' => 'Город не найден'], 404);
            }

            // Сохраняем город в сессии
            Session::put('city_id', $city->id);
            Session::put('city_name', $city->name);

            return response()->json([
                'success' => true,
                'city' => [
                    'id' => $city->id,
                    'name' => $city->name,
                    'slug' => $city->slug,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Некорректные данные'], 400);
        } catch (\Exception $e) {
            Log::error('Ошибка при выборе города: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка при установке города'], 500);
        }
    }
}
