<?php

namespace App\Http\Controllers;

use App\Models\SiteNotice;
use App\Services\SiteNoticeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** API для модальных уведомлений на сайте (см. partials/site-notices.blade.php). */
class SiteNoticeController extends Controller
{
    /** Какие уведомления показать этому посетителю. */
    public function pending(Request $request, SiteNoticeService $service): JsonResponse
    {
        $user = $request->user();

        // Предпросмотр для админа: показать выбранное уведомление, не глядя на расписание и аудиторию
        if ($user && ($user->is_admin ?? false) && $request->filled('preview')) {
            $notice = SiteNotice::find((int) $request->query('preview'));

            return $this->json(['notices' => $notice ? [$notice->toPayload(true)] : []]);
        }

        $key = $service->visitorKey($user, $request->query('vid'));

        if (! $key) {
            return $this->json(['notices' => []]);
        }

        $notices = $service->pending($user, session('city_id') ? (int) session('city_id') : null, $key);

        return $this->json(['notices' => $notices->map(fn (SiteNotice $n) => $n->toPayload())->values()]);
    }

    /** Уведомление показано — запоминаем (для частоты и статистики). */
    public function seen(Request $request, SiteNotice $notice, SiteNoticeService $service): JsonResponse
    {
        $key = $service->visitorKey($request->user(), $request->input('vid'));

        if (! $key || ! $notice->is_active) {
            return response()->json(['ok' => false], 422);
        }

        $service->markSeen($notice, $key);

        return response()->json(['ok' => true]);
    }

    private function json(array $data): JsonResponse
    {
        // Ответ зависит от посетителя — кэшировать его нельзя
        return response()->json($data)->header('Cache-Control', 'no-store, max-age=0');
    }
}
