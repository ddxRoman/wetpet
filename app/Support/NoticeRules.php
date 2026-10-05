<?php

namespace App\Support;

/**
 * Правила показа уведомлений — чистые функции без обращения к базе (удобно проверять и читать).
 */
class NoticeRules
{
    /**
     * Попадает ли текущее время суток («HH:MM») в окно показа.
     * Поддерживается окно через полночь: с 22:00 до 06:00. Пустое окно — всегда можно.
     */
    public static function inDailyWindow(?string $from, ?string $until, string $nowHm): bool
    {
        $from  = $from  ? substr($from, 0, 5)  : null;
        $until = $until ? substr($until, 0, 5) : null;

        if (! $from && ! $until) {
            return true;
        }

        if ($from && ! $until) {
            return $nowHm >= $from;
        }

        if (! $from && $until) {
            return $nowHm <= $until;
        }

        return $from <= $until
            ? ($nowHm >= $from && $nowHm <= $until)
            : ($nowHm >= $from || $nowHm <= $until);
    }

    /**
     * Разрешает ли частота показать уведомление ещё раз.
     *
     * @param int|null $lastShown unix-время прошлого показа этому посетителю (null — не показывали)
     */
    public static function frequencyAllows(string $type, ?int $value, ?int $lastShown, int $now): bool
    {
        return match ($type) {
            'once'    => $lastShown === null,
            'hours'   => $lastShown === null || $now - $lastShown >= max(1, (int) $value) * 3600,
            'days'    => $lastShown === null || $now - $lastShown >= max(1, (int) $value) * 86400,
            // «session» (раз за визит) считает браузер, «always» — показывать при каждой загрузке страницы
            default   => true,
        };
    }

    /**
     * Подходит ли посетитель под аудиторию уведомления.
     *
     * @param array{user_scope?:string,species?:?array,regions?:?array,city_ids?:?array} $rule
     * @param int[]    $cityIds       города посетителя (выбранный в шапке и из профиля)
     * @param string[] $regionsLower  регионы этих городов в нижнем регистре
     * @param string[] $speciesLower  виды его живых питомцев в нижнем регистре
     */
    public static function audienceMatches(array $rule, bool $isAuth, array $cityIds, array $regionsLower, array $speciesLower): bool
    {
        $scope = $rule['user_scope'] ?? 'all';

        if ($scope === 'auth' && ! $isAuth) {
            return false;
        }

        if ($scope === 'guests' && $isAuth) {
            return false;
        }

        // Владельцы определённых животных (гости питомцев не имеют)
        $species = array_values(array_filter(array_map(fn ($s) => mb_strtolower(trim((string) $s)), (array) ($rule['species'] ?? []))));
        if ($species && ! array_intersect($species, $speciesLower)) {
            return false;
        }

        // Жители определённых городов ИЛИ регионов
        $wantCities  = array_values(array_filter(array_map('intval', (array) ($rule['city_ids'] ?? []))));
        $wantRegions = array_values(array_filter(array_map(fn ($r) => mb_strtolower(trim((string) $r)), (array) ($rule['regions'] ?? []))));

        if ($wantCities || $wantRegions) {
            $inCity   = $wantCities && array_intersect($wantCities, array_map('intval', $cityIds));
            $inRegion = $wantRegions && array_intersect($wantRegions, $regionsLower);

            if (! $inCity && ! $inRegion) {
                return false;
            }
        }

        return true;
    }
}
