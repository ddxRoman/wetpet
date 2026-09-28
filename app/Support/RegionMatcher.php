<?php

namespace App\Support;

use App\Models\City;

/**
 * Находит в поисковой строке названия регионов («Краснодарский край», «Краснодарский»)
 * и вырезает их из текста поиска.
 */
class RegionMatcher
{
    private const GENERIC = '/\s*(автономный округ|автономная область|край|область|республика|округ)\s*/u';

    /** @return array{0: string, 1: array<int,string>} [очищенный запрос, найденные регионы] */
    public static function extract(string $term): array
    {
        $regions = City::whereNotNull('region')->where('region', '!=', '')->distinct()->pluck('region');

        $found = [];
        $stripped = $term;

        foreach ($regions as $region) {
            $full = mb_strtolower(trim($region));
            $core = trim(preg_replace(self::GENERIC, ' ', $full));
            $candidates = [$full];
            if (mb_strlen($core) >= 5 && $core !== $full) {
                $candidates[] = $core;
            }

            foreach ($candidates as $candidate) {
                $pattern = '/(?<![\p{L}\p{N}])' . preg_quote($candidate, '/') . '(?![\p{L}\p{N}])/iu';
                if (preg_match($pattern, $stripped)) {
                    $found[] = $region;
                    $stripped = trim(preg_replace($pattern, ' ', $stripped));
                    break;
                }
            }
        }

        $stripped = trim(preg_replace('/\s+/u', ' ', $stripped));

        // Запрос состоял только из названия региона — ищем по исходному тексту
        if ($found && mb_strlen($stripped) < 2) {
            $stripped = $term;
        }

        return [$stripped, array_values(array_unique($found))];
    }
}
