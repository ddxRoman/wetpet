<?php

namespace App\Services;

use App\Models\City;
use App\Models\Pet;
use App\Models\SiteNotice;
use App\Models\SiteNoticeView;
use App\Models\User;
use App\Support\NoticeRules;
use Illuminate\Support\Collection;

/**
 * Какие уведомления нужно показать этому посетителю прямо сейчас.
 * Сами правила (окно времени, частота, аудитория) — в App\Support\NoticeRules.
 */
class SiteNoticeService
{
    /** Не больше стольких уведомлений за одну загрузку страницы (показываются по очереди). */
    private const MAX_PER_PAGE = 3;

    /** Ключ посетителя: u:{id} у вошедших, g:{uuid из браузера} у гостей. */
    public function visitorKey(?User $user, ?string $vid): ?string
    {
        if ($user) {
            return 'u:' . $user->id;
        }

        return ($vid && preg_match('/^[A-Za-z0-9\-]{8,64}$/', $vid)) ? 'g:' . $vid : null;
    }

    /**
     * @param int|null $sessionCityId город, выбранный/определённый для посетителя (session city_id)
     */
    public function pending(?User $user, ?int $sessionCityId, string $visitorKey): Collection
    {
        $now = now();

        $notices = SiteNotice::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderByDesc('priority')
            ->orderBy('id')
            ->get();

        if ($notices->isEmpty()) {
            return collect();
        }

        $views = SiteNoticeView::query()
            ->where('visitor_key', $visitorKey)
            ->whereIn('site_notice_id', $notices->pluck('id'))
            ->get()
            ->keyBy('site_notice_id');

        // Контекст посетителя считаем один раз
        $cityIds = array_values(array_unique(array_filter([$sessionCityId, $user?->city_id])));
        $regions = $cityIds
            ? City::whereIn('id', $cityIds)->pluck('region')->filter()
                ->map(fn ($r) => mb_strtolower(trim($r)))->unique()->values()->all()
            : [];
        $species = $user ? $this->ownedSpecies($user) : [];

        $nowHm = $now->copy()->timezone(SiteNotice::TIMEZONE)->format('H:i');

        return $notices
            ->filter(function (SiteNotice $n) use ($user, $cityIds, $regions, $species, $views, $nowHm, $now) {
                if (! NoticeRules::inDailyWindow($n->daily_from, $n->daily_until, $nowHm)) {
                    return false;
                }

                $lastShown = $views->get($n->id)?->last_shown_at?->getTimestamp();

                if (! NoticeRules::frequencyAllows($n->frequency_type, $n->frequency_value, $lastShown, $now->getTimestamp())) {
                    return false;
                }

                return NoticeRules::audienceMatches([
                    'user_scope' => $n->user_scope,
                    'species'    => $n->species,
                    'regions'    => $n->regions,
                    'city_ids'   => $n->city_ids,
                ], (bool) $user, $cityIds, $regions, $species);
            })
            ->take(self::MAX_PER_PAGE)
            ->values();
    }

    /** Виды животных, которые есть у пользователя (живые питомцы), в нижнем регистре. */
    private function ownedSpecies(User $user): array
    {
        return Pet::query()
            ->where('pets.user_id', $user->id)
            ->whereNull('pets.death_date')
            ->join('animals', 'animals.id', '=', 'pets.animal_id')
            ->pluck('animals.species')
            ->filter()
            ->map(fn ($s) => mb_strtolower(trim($s)))
            ->unique()
            ->values()
            ->all();
    }

    /** Фиксируем показ: от этого зависит «раз в N часов/дней» и статистика. */
    public function markSeen(SiteNotice $notice, string $visitorKey): void
    {
        try {
            $view = SiteNoticeView::firstOrCreate(
                ['site_notice_id' => $notice->id, 'visitor_key' => $visitorKey],
                ['last_shown_at' => now(), 'shown_count' => 0]
            );
        } catch (\Throwable $e) {
            // гонка двух одновременных запросов: запись уже создана — берём её
            $view = SiteNoticeView::where('site_notice_id', $notice->id)->where('visitor_key', $visitorKey)->first();
        }

        $view?->increment('shown_count', 1, ['last_shown_at' => now()]);
    }
}
