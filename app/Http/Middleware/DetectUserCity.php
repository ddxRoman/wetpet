<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\City;
use Illuminate\Support\Facades\Auth;

class DetectUserCity
{
    public function handle(Request $request, Closure $next)
    {
        // 1. ПРИОРИТЕТ №1: Проверяем, есть ли уже город в сессии (ручной выбор)
        // Если пользователь уже выбрал город сам, мы ничего не перезаписываем.
        if (session()->has('city_id')) {
            return $next($request);
        }

        // 2. ПРИОРИТЕТ №2: Если в сессии пусто, проверяем авторизацию
        // Если юзер залогинен, берем город из его профиля в БД
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->city_id) {
                $city = City::find($user->city_id);
                if ($city) {
                    $this->setCityToSession($city, false); // false означает, что это не авто-определение
                    return $next($request);
                }
            }
        }

        // 3. ПРИОРИТЕТ №3: Только если в сессии и БД пусто — идем в GeoIP
        try {
            $ip = $request->ip();
            
            // Пропускаем локальный IP, чтобы не тратить запросы
            if ($ip === '127.0.0.1' || $ip === '::1') {
                return $next($request);
            }

            $response = Http::timeout(3)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get("https://ipwho.is/{$ip}");

            if ($response->ok()) {
                $data = $response->json();

                if (($data['success'] ?? false) === true && !empty($data['city'])) {
                    $cityName = trim($data['city']);
                    $citySlug = Str::slug($cityName);

                    // Ищем город в нашей базе
                    $city = City::where('slug', $citySlug)
                                ->orWhereRaw('LOWER(name) = LOWER(?)', [$cityName])
                                ->first();

                    if ($city) {
                        // Записываем в сессию как авто-определенный
                        $this->setCityToSession($city, true);
                        
                        // ВНИМАНИЕ: Мы НЕ вызываем $user->update(). 
                        // Город остается только в сессии до тех пор, пока юзер сам не сменит его в ЛК.
                    }
                }
            }
        } catch (\Throwable $e) {
            logger()->error('DetectUserCity GeoIP Error: ' . $e->getMessage());
        }

        return $next($request);
    }

    /**
     * Вспомогательный метод для записи данных в сессию
     */
    private function setCityToSession($city, $isAuto = false)
    {
        session([
            'city_id'   => $city->id,
            'city_name' => $city->name,
            'city_slug' => $city->slug,
            'city_auto' => $isAuto, // Метка, что город определен автоматически
        ]);
    }
}