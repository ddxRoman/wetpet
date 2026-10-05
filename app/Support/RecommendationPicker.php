<?php

namespace App\Support;

/**
 * Выбор клиник для слайдера «Рекомендации для вас в городе» из клиник ОДНОГО города.
 * Чистая функция без обращения к базе: на вход — клиники города с рейтингом и числом отзывов.
 *
 * Правила:
 *  - «надёжные» — клиники, у которых не меньше MIN_REVIEWS отзывов; сначала берутся они;
 *  - если надёжных с рейтингом от HIGH_RATING набирается не меньше $limit — выбираем из них случайные
 *    (чтобы слайдер не был одинаковым при каждом заходе);
 *  - иначе сортируем по убыванию: надёжные раньше остальных, затем по рейтингу, затем по числу отзывов.
 *    Так в маленьком городе с парой клиник слайдер всё равно покажет лучшие из имеющихся.
 */
class RecommendationPicker
{
    public const MIN_REVIEWS = 5;
    public const HIGH_RATING = 4.7;

    /**
     * @param  array<int, array{id:int, avg:float, count:int}>  $rows
     * @param  callable|null  $shuffle  функция перемешивания массива (для тестов можно подставить свою)
     * @return array<int, array{id:int, avg:float, count:int}>
     */
    public static function pick(array $rows, int $limit = 5, ?callable $shuffle = null): array
    {
        $rows = array_values($rows);

        if (! $rows) {
            return [];
        }

        $reliable = array_values(array_filter($rows, fn ($r) => $r['count'] >= self::MIN_REVIEWS));
        $high     = array_values(array_filter($reliable, fn ($r) => $r['avg'] >= self::HIGH_RATING));

        if (count($high) >= $limit) {
            $shuffle ??= function (array $items) {
                shuffle($items);

                return $items;
            };

            return array_slice(array_values($shuffle($high)), 0, $limit);
        }

        usort($rows, function ($a, $b) {
            $ra = (int) ($a['count'] >= self::MIN_REVIEWS);
            $rb = (int) ($b['count'] >= self::MIN_REVIEWS);

            return [$rb, $b['avg'], $b['count']] <=> [$ra, $a['avg'], $a['count']];
        });

        return array_slice($rows, 0, $limit);
    }
}
