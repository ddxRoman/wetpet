<?php

namespace App\Support;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Защита форм добавления (специалист, врач, клиника, организация) от повторной отправки:
 * двойной клик, два обработчика на форме, повтор запроса из-за сети.
 *
 * Идентичные запросы (тот же пользователь, то же название и город) выполняются по очереди.
 * Если первый уже успешно создал запись, повторный запрос получает тот же JSON-ответ,
 * а новая запись не создаётся и повторное уведомление в Telegram не отправляется.
 */
class DuplicateSubmissionGuard
{
    private const RESULT_TTL_SECONDS = 30;

    public static function run(Request $request, string $scope, Closure $callback)
    {
        $fingerprint = $scope . ':' . (auth()->id() ?? $request->ip()) . ':' . md5(
            mb_strtolower(trim((string) $request->input('name'))) . '|' . $request->input('city_id')
        );
        $resultKey = 'dup-guard-result:' . $fingerprint;

        $lock = null;

        try {
            $lock = Cache::lock('dup-guard-lock:' . $fingerprint, 20);
            $lock->block(10);
        } catch (\Throwable $e) {
            // Хранилище кэша без блокировок или таймаут — работаем как раньше
            $lock = null;
        }

        try {
            if ($cached = Cache::get($resultKey)) {
                return response()->json($cached);
            }

            $response = $callback();

            if ($response instanceof JsonResponse) {
                $data = $response->getData(true);
                if (! empty($data['success'])) {
                    Cache::put($resultKey, $data, self::RESULT_TTL_SECONDS);
                }
            }

            return $response;
        } finally {
            if ($lock) {
                try {
                    $lock->release();
                } catch (\Throwable $e) {
                    // блокировка уже истекла — ничего страшного
                }
            }
        }
    }
}
