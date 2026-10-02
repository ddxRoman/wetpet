<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Нечёткий поиск по названиям: прощает опечатки, пропущенные и лишние буквы,
 * перестановку соседних букв, регистр, а также пробелы, дефисы и кавычки
 * («Вет Макс», «ВетМакс», «Вет-Макс» и «ветмакс» — одно и то же).
 *
 * Принцип: и запрос, и названия приводятся к виду «только буквы и цифры в нижнем регистре,
 * ё = е», после чего ищется ПОДСТРОКА названия, отличающаяся от запроса не более чем
 * на допустимое число правок (расстояние Дамерау — Левенштейна). Допустимое число правок
 * зависит от длины запроса, а очень короткие запросы (до 4 символов) не размываются.
 */
class FuzzySearch
{
    /** Минимальная длина запроса (после нормализации), с которой включается нечёткий поиск. */
    private const MIN_LENGTH = 4;

    /** Сколько секунд хранить индекс названий: новые записи появятся в нечётком поиске не позже. */
    private const CACHE_SECONDS = 60;

    /** Оставляем только буквы и цифры, нижний регистр, ё → е. */
    public static function normalize(?string $text): string
    {
        $text = mb_strtolower((string) $text);
        $text = str_replace('ё', 'е', $text);

        return preg_replace('/[^\p{L}\p{N}]+/u', '', $text) ?? '';
    }

    /** Сколько правок допустимо для запроса данной длины. */
    public static function maxDistance(int $length): int
    {
        if ($length < self::MIN_LENGTH) {
            return -1;      // нечёткий поиск не применяется
        }

        return match (true) {
            $length <= 6  => 1,
            $length <= 10 => 2,
            default       => 3,
        };
    }

    /**
     * Наименьшее число правок, на которое запрос отличается от какой-либо подстроки текста
     * (алгоритм Селлерса с учётом перестановки соседних букв). 0 — запрос встречается как есть.
     */
    public static function distance(string $needle, string $haystack): int
    {
        if ($needle === '') {
            return 0;
        }

        if ($haystack !== '' && mb_strpos($haystack, $needle) !== false) {
            return 0;
        }

        $p = mb_str_split($needle);
        $t = mb_str_split($haystack);
        $m = count($p);
        $n = count($t);

        // prev2/prev/cur — строки таблицы по буквам запроса; начало подстроки выбирается свободно
        $prev2 = null;
        $prev  = range(0, $m);   // для j = 0: расстояние = числу букв запроса
        $best  = $prev[$m];

        for ($j = 1; $j <= $n; $j++) {
            $cur = [0];           // подстрока может начаться с любой позиции текста

            for ($i = 1; $i <= $m; $i++) {
                $cost = $p[$i - 1] === $t[$j - 1] ? 0 : 1;

                $value = min(
                    $prev[$i - 1] + $cost,   // замена / совпадение
                    $prev[$i] + 1,           // лишняя буква в тексте
                    $cur[$i - 1] + 1         // пропущенная буква в тексте
                );

                // перестановка соседних букв («макс» ↔ «масс»-подобные опечатки: «кам» ↔ «кма»)
                if ($i > 1 && $j > 1 && $p[$i - 1] === $t[$j - 2] && $p[$i - 2] === $t[$j - 1] && $prev2 !== null) {
                    $value = min($value, $prev2[$i - 2] + 1);
                }

                $cur[$i] = $value;
            }

            $best  = min($best, $cur[$m]);
            $prev2 = $prev;
            $prev  = $cur;
        }

        return $best;
    }

    /** Подходит ли запрос под текст с учётом допустимого числа опечаток. */
    public static function matches(string $query, string $text): bool
    {
        $q = self::normalize($query);
        $t = self::normalize($text);
        $k = self::maxDistance(mb_strlen($q));

        if ($k < 0 || $t === '') {
            return false;
        }

        // Подстрока не может быть короче запроса больше чем на k символов
        if (mb_strlen($t) + $k < mb_strlen($q)) {
            return false;
        }

        return self::distance($q, $t) <= $k;
    }

    /**
     * id записей модели, у которых хотя бы в одной из колонок найдено нечёткое совпадение
     * с любым из запросов (например, обычный запрос и тот же запрос в другой раскладке).
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     * @param  string[]  $columns
     * @param  string[]  $queries
     * @return int[]
     */
    public static function ids(string $modelClass, array $columns, array $queries): array
    {
        $needles = [];
        foreach ($queries as $query) {
            $normalized = self::normalize($query);

            if (self::maxDistance(mb_strlen($normalized)) >= 0) {
                $needles[$normalized] = self::maxDistance(mb_strlen($normalized));
            }
        }

        if (! $needles) {
            return [];
        }

        $index = self::index($modelClass, $columns);
        $ids   = [];

        foreach ($index as $id => $values) {
            foreach ($values as $value) {
                if ($value === '') {
                    continue;
                }

                foreach ($needles as $needle => $k) {
                    if (mb_strlen($value) + $k < mb_strlen((string) $needle)) {
                        continue;
                    }

                    if (self::distance((string) $needle, $value) <= $k) {
                        $ids[] = (int) $id;
                        continue 3;
                    }
                }
            }
        }

        return $ids;
    }

    /** Индекс «id → нормализованные значения колонок», кэшируется на минуту. */
    private static function index(string $modelClass, array $columns): array
    {
        $key = 'fuzzy_index:' . md5($modelClass . '|' . implode(',', $columns));

        return Cache::remember($key, self::CACHE_SECONDS, function () use ($modelClass, $columns) {
            $index = [];

            $modelClass::query()->get(array_merge(['id'], $columns))->each(function ($row) use (&$index, $columns) {
                $index[$row->id] = array_map(fn ($column) => self::normalize($row->{$column}), $columns);
            });

            return $index;
        });
    }
}
