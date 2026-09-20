<?php

namespace App\Support;

use Carbon\Carbon;

class AgeFormatter
{
    /**
     * Русское склонение: 1 год / 2 года / 5 лет.
     */
    public static function plural(int $n, string $one, string $few, string $many): string
    {
        $n = abs($n) % 100;
        $last = $n % 10;

        if ($n > 10 && $n < 20) {
            return $many;
        }
        if ($last > 1 && $last < 5) {
            return $few;
        }
        if ($last === 1) {
            return $one;
        }

        return $many;
    }

    /**
     * Точный промежуток времени от $from до $to (по умолчанию — до текущего
     * момента), без округления: «1 год 3 месяца 12 дней». Нулевые части
     * пропускаются. $to нужен, например, для возраста умершего питомца.
     *
     * $withDays = false — только годы и месяцы (для возраста питомцев).
     * Если прошло меньше минимальной единицы — вернёт пустую строку,
     * подставить заглушку («меньше суток») должен вызывающий код.
     */
    public static function since($from, bool $withDays = true, $to = null): string
    {
        $from = Carbon::parse($from);
        $end  = $to ? Carbon::parse($to) : Carbon::now();

        if ($from->greaterThan($end)) {
            return '';
        }

        $diff  = $from->diff($end);
        $years  = (int) $diff->y;
        $months = (int) $diff->m;
        $days   = (int) $diff->d;

        $parts = [];

        if ($years) {
            $parts[] = $years . ' ' . self::plural($years, 'год', 'года', 'лет');
        }
        if ($months) {
            $parts[] = $months . ' ' . self::plural($months, 'месяц', 'месяца', 'месяцев');
        }
        if ($withDays && $days) {
            $parts[] = $days . ' ' . self::plural($days, 'день', 'дня', 'дней');
        }

        return implode(' ', $parts);
    }
}
