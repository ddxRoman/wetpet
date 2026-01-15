<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\City;
use App\Http\Controllers\SpecialistCreateController;

class DetectUserCity
{
    public function handle(Request $request, Closure $next)
    {
        logger()->info('=== DetectUserCity START ===');

        try {
            // STEP 1 — IP
            $ip = $request->ip();
            logger()->info('STEP 1: IP', ['ip' => $ip]);

            // STEP 2 — ipwho.is
            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get("https://ipwho.is/{$ip}");

            logger()->info('STEP 2: HTTP STATUS', [
                'status' => $response->status(),
            ]);

            if (! $response->ok()) {
                logger()->warning('STEP 2 FAILED: response not ok');
                return $next($request);
            }

            $data = $response->json();

            logger()->info('STEP 3: RAW RESPONSE', $data);

            if (($data['success'] ?? false) !== true) {
                logger()->warning('STEP 3 FAILED: success !== true');
                return $next($request);
            }

            if (empty($data['city'])) {
                logger()->warning('STEP 3 FAILED: city empty');
                return $next($request);
            }

            // STEP 4 — normalize
            $cityName = trim($data['city']);
            $citySlug = Str::slug($cityName);

            logger()->info('STEP 4: NORMALIZED', [
                'city_name' => $cityName,
                'city_slug' => $citySlug,
            ]);

            // STEP 5 — DB by slug
            logger()->info('STEP 5: DB search by slug');
            $city = City::where('slug', $citySlug)->first();

            if ($city) {
                logger()->info('STEP 5 SUCCESS', [
                    'id' => $city->id,
                    'name' => $city->name,
                ]);
            } else {
                logger()->warning('STEP 5 FAILED');
            }

            // STEP 6 — DB by name
            if (! $city) {
                logger()->info('STEP 6: DB search by name');
                $city = City::whereRaw(
                    'LOWER(name) = LOWER(?)',
                    [$cityName]
                )->first();

                if ($city) {
                    logger()->info('STEP 6 SUCCESS', [
                        'id' => $city->id,
                        'name' => $city->name,
                    ]);
                } else {
                    logger()->warning('STEP 6 FAILED');
                }
            }

            // STEP 7 — session
            if ($city) {
                session([
                    'city_id'   => $city->id,
                    'city_name' => $city->name,
                    'city_slug' => $city->slug,
                    'city_auto' => true,
                ]);

                logger()->info('STEP 7: SESSION SAVED', session()->all());
            }

        } catch (\Throwable $e) {
            logger()->error('DetectUserCity EXCEPTION', [
                'message' => $e->getMessage(),
            ]);
        }

        logger()->info('=== DetectUserCity END ===');

        return $next($request);
    }
}
