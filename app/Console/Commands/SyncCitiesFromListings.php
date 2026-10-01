<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Clinic;
use App\Models\Organization;
use App\Services\CityResolver;
use Illuminate\Console\Command;

/**
 * Переносит города из текстовых полей clinics.city / organizations.city в таблицу cities.
 *
 * Город создаётся под тем регионом, который записан у клиники/организации, если такой
 * регион уже есть в cities (сравнение без учёта регистра и лишних пробелов). Город с тем же
 * названием в том же регионе повторно не создаётся. Записи с неизвестным или пустым регионом
 * не трогаются — они выводятся списком, чтобы вы поправили регион вручную и запустили команду снова.
 *
 * Запуск:
 *   php artisan cities:sync-from-listings --dry-run   (только показать, что будет сделано)
 *   php artisan cities:sync-from-listings             (создать города)
 */
class SyncCitiesFromListings extends Command
{
    protected $signature = 'cities:sync-from-listings
                            {--dry-run : Ничего не создавать, только показать результат}
                            {--verified=confirmed : Статус новых городов (confirmed|unconfirmed)}';

    protected $description = 'Добавляет в cities города из clinics.city и organizations.city (large_city = 0) под нужные регионы';

    public function handle(CityResolver $resolver): int
    {
        $dry      = (bool) $this->option('dry-run');
        $verified = $this->option('verified') === 'unconfirmed' ? 'unconfirmed' : 'confirmed';

        // Справочник: какие регионы и города уже есть (ключи в нижнем регистре)
        $regions = [];   // region(lower) => region как записан в cities
        $exists  = [];   // "name|region" (lower) => true
        $byName  = [];   // name(lower) => true — для записей без региона

        foreach (City::query()->get(['name', 'region']) as $c) {
            $region = mb_strtolower(trim((string) $c->region));
            $name   = mb_strtolower(trim((string) $c->name));

            $regions[$region] ??= trim((string) $c->region);
            $exists[$name . '|' . $region] = true;
            $byName[$name] = true;
        }

        // Уникальные пары (город, регион) из клиник и организаций
        $pairs = [];
        foreach ([['clinics', Clinic::class], ['organizations', Organization::class]] as [$label, $model]) {
            $model::query()
                ->whereNotNull('city')->where('city', '!=', '')
                ->select(['city', 'region'])
                ->distinct()
                ->get()
                ->each(function ($row) use (&$pairs, $resolver, $label) {
                    $name   = $resolver->normalizeName($row->city);
                    $region = trim((string) $row->region);
                    $key    = mb_strtolower($name) . '|' . mb_strtolower($region);

                    $pairs[$key] ??= ['name' => $name, 'region' => $region, 'sources' => []];
                    $pairs[$key]['sources'][$label] = true;
                });
        }

        $created = [];
        $skipped = 0;
        $unknownRegion = [];
        $noRegion = [];

        foreach ($pairs as $pair) {
            $nameKey   = mb_strtolower($pair['name']);
            $regionKey = mb_strtolower($pair['region']);

            if ($pair['region'] === '') {
                // Регион не указан: если такой город уже есть хоть в каком-то регионе — ок, иначе привязать некуда
                if (isset($byName[$nameKey])) {
                    $skipped++;
                } else {
                    $noRegion[] = $pair['name'];
                }
                continue;
            }

            if (! isset($regions[$regionKey])) {
                $unknownRegion[$pair['region']][] = $pair['name'];
                continue;
            }

            if (isset($exists[$nameKey . '|' . $regionKey])) {
                $skipped++;
                continue;
            }

            if (! $dry) {
                $resolver->findOrCreate($pair['name'], $regions[$regionKey], null, $verified);
            }

            $exists[$nameKey . '|' . $regionKey] = true;
            $byName[$nameKey] = true;
            $created[] = [$pair['name'], $regions[$regionKey], implode(', ', array_keys($pair['sources']))];
        }

        $this->info(($dry ? '[dry-run] ' : '') . 'Пар «город — регион» в клиниках и организациях: ' . count($pairs));
        $this->line('Уже есть в cities: ' . $skipped);
        $this->line(($dry ? 'Будет создано: ' : 'Создано: ') . count($created));

        if ($created) {
            $this->table(['Город', 'Регион', 'Где встречается'], $created);
        }

        if ($unknownRegion) {
            $this->warn('Регион не найден в cities — эти города не созданы (проверьте написание региона у записи):');
            foreach ($unknownRegion as $region => $names) {
                $this->line('  ' . $region . ': ' . implode(', ', array_slice($names, 0, 15)) . (count($names) > 15 ? ' …' : ''));
            }
        }

        if ($noRegion) {
            $this->warn('У записей не указан регион, города не созданы: ' . implode(', ', array_slice($noRegion, 0, 30)));
        }

        return self::SUCCESS;
    }
}
