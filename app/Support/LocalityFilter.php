<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Запоминает выбор фильтра «Другие населённые пункты» (сессия + cookie на год),
 * чтобы пользователю всегда показывались только такие записи, пока он не выключит фильтр.
 */
class LocalityFilter
{
    private const KEY = 'other_localities_only';

    public static function resolve(Request $request): bool
    {
        if ($request->has('other_localities')) {
            $value = $request->boolean('other_localities');
            session([self::KEY => $value]);
            Cookie::queue(self::KEY, $value ? '1' : '0', 60 * 24 * 365);

            return $value;
        }

        if (session()->has(self::KEY)) {
            return (bool) session(self::KEY);
        }

        return $request->cookie(self::KEY) === '1';
    }
}
