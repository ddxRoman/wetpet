<?php

namespace App\Models\Concerns;

use App\Models\City;
use Illuminate\Database\Eloquent\Builder;

/**
 * Для организаций и клиник: у них город и регион хранятся текстом.
 * «Другой населённый пункт» — город записи не найден в справочнике cities
 * (например «хутор Ленина»).
 */
trait HasLocalityScopes
{
    private static ?array $knownCityNames = null;

    // Записи с городом, которого нет в таблице cities
    public function scopeOtherLocality(Builder $query): Builder
    {
        return $query
            ->whereRaw("TRIM(COALESCE(city, '')) != ''")
            ->whereRaw('LOWER(TRIM(city)) NOT IN (SELECT LOWER(TRIM(name)) FROM cities)');
    }

    public function scopeInRegion(Builder $query, ?string $region): Builder
    {
        return $query->whereRaw('LOWER(TRIM(region)) = LOWER(TRIM(?))', [$region]);
    }

    /**
     * Выборка для каталога.
     *  - $otherOnly = true  — только «другие населённые пункты» (в регионе выбранного города, если он есть);
     *  - иначе — записи выбранного города + «другие населённые пункты» его региона.
     */
    public function scopeForCatalog(Builder $query, ?City $city, bool $otherOnly): Builder
    {
        $region = $city?->region;

        if ($otherOnly) {
            return $query->otherLocality()
                ->when($region, fn ($q) => $q->inRegion($region));
        }

        if (! $city) {
            return $query;
        }

        return $query->where(function ($w) use ($city, $region) {
            $w->whereRaw('LOWER(TRIM(city)) = LOWER(TRIM(?))', [$city->name]);

            if ($region) {
                $w->orWhere(fn ($x) => $x->otherLocality()->inRegion($region));
            }
        });
    }

    // Записи выбранного города — выше, «другие населённые пункты» — ниже
    public function scopeLocalFirst(Builder $query, ?City $city): Builder
    {
        if (! $city) {
            return $query;
        }

        return $query->orderByRaw(
            'CASE WHEN LOWER(TRIM(city)) = LOWER(TRIM(?)) THEN 0 ELSE 1 END',
            [$city->name]
        );
    }


    // Условие «запись относится к региону»: по полю region записи
    // или по её городу, который в справочнике cities принадлежит этому региону
    private static function regionSql(): string
    {
        return '(LOWER(TRIM(region)) = ? OR LOWER(TRIM(city)) IN '
            . '(SELECT LOWER(TRIM(name)) FROM cities WHERE LOWER(TRIM(region)) = ?))';
    }

    /**
     * Поиск: выбранный город + «другие населённые пункты» его региона
     * + все записи регионов, которые пользователь явно указал в запросе.
     */
    public function scopeForSearch(Builder $query, ?City $city, array $regions = []): Builder
    {
        if (! $city && empty($regions)) {
            return $query;
        }

        return $query->where(function ($w) use ($city, $regions) {
            foreach ($regions as $region) {
                $r = mb_strtolower(trim($region));
                $w->orWhereRaw(self::regionSql(), [$r, $r]);
            }

            if ($city) {
                $w->orWhereRaw('LOWER(TRIM(city)) = LOWER(TRIM(?))', [$city->name]);

                if ($city->region) {
                    $w->orWhere(fn ($x) => $x->otherLocality()->inRegion($city->region));
                }
            }
        });
    }

    /**
     * Порядок в поиске: сначала указанные в запросе регионы (если они отличаются от региона
     * пользователя), затем выбранный город, затем остальные населённые пункты региона.
     */
    public function scopeSearchRank(Builder $query, ?City $city, array $regions = []): Builder
    {
        $parts = [];
        $bindings = [];

        foreach ($regions as $region) {
            $r = mb_strtolower(trim($region));
            $parts[] = 'WHEN ' . self::regionSql() . ' THEN 0';
            array_push($bindings, $r, $r);
        }

        if ($city) {
            $parts[] = 'WHEN LOWER(TRIM(city)) = LOWER(TRIM(?)) THEN 1';
            $bindings[] = $city->name;
        }

        if (! $parts) {
            return $query;
        }

        return $query->orderByRaw('CASE ' . implode(' ', $parts) . ' ELSE 2 END', $bindings);
    }

    // Для пометки «Другой населённый пункт» на карточке
    public function getIsOtherLocalityAttribute(): bool
    {
        $name = mb_strtolower(trim((string) $this->city));
        if ($name === '') {
            return false;
        }

        self::$knownCityNames ??= City::pluck('name')
            ->map(fn ($n) => mb_strtolower(trim($n)))
            ->flip()
            ->all();

        return ! isset(self::$knownCityNames[$name]);
    }
}
