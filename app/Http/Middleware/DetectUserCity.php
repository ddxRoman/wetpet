<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\City;

class DetectUserCity
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // 1. Если пользователь авторизован — не GeoIP
            if (auth()->check()) {
                return $next($request);
            }

            // 2. Если город уже есть в сессии — не трогаем
            if (session()->has('city_id')) {
                return $next($request);
            }

            $ip = $request->ip();

            if (
                $ip === '127.0.0.1' ||
                $ip === '::1' ||
                !filter_var($ip, FILTER_VALIDATE_IP)
            ) {
                return $next($request);
            }

            $response = @file_get_contents(
                "http://ip-api.com/json/{$ip}?fields=status,country,regionName,city"
            );

            if (! $response) {
                return $next($request);
            }

            $data = json_decode($response, true);

            if (($data['status'] ?? null) !== 'success') {
                return $next($request);
            }

            if (empty($data['city'])) {
                return $next($request);
            }

            // 🔍 Ищем город в БД
            $city = City::where('name', $data['city'])->first();

            if (! $city) {
                return $next($request);
            }

            session([
                'city_id'   => $city->id,
                'city_name' => $city->name,
                'city_auto' => true,
            ]);

        } catch (\Throwable $e) {
            logger()->error('DetectUserCity error', [
                'message' => $e->getMessage(),
            ]);
        }

        return $next($request);
    }
}
