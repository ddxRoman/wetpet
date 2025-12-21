<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DetectUserCity
{
    public function handle(Request $request, Closure $next)
    {
        try {

            // Если уже определяли — не трогаем
            if (session()->has('user_city')) {
                return $next($request);
            }

            $ip = $request->ip();

            // Локалка / невалидный IP
            if (
                $ip === '127.0.0.1' ||
                $ip === '::1' ||
                !filter_var($ip, FILTER_VALIDATE_IP)
            ) {
                return $next($request);
            }

            $url = "http://ip-api.com/json/{$ip}?fields=status,country,regionName,city";

            $response = @file_get_contents($url);

            if (! $response) {
                return $next($request);
            }

            $data = json_decode($response, true);

            if (($data['status'] ?? null) !== 'success') {
                return $next($request);
            }

            if (!empty($data['city'])) {
                session([
                    'user_city'   => $data['city'],
                    'user_region' => $data['regionName'] ?? null,
                    'user_country'=> $data['country'] ?? null,
                ]);
            }

        } catch (\Throwable $e) {

            // ❗ НИКОГДА не роняем сайт
            logger()->error('DetectUserCity error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
        }

        return $next($request);
    }
}
