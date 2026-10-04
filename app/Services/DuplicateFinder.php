<?php

namespace App\Services;

use App\Models\City;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Organization;
use App\Models\Specialist;
use App\Support\FuzzySearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Поиск дублей при создании клиники, организации, врача, специалиста и населённого пункта.
 *
 * Сравниваются название (имя), адрес, город и телефон. Каждому совпадению начисляются баллы,
 * в результат попадают записи с суммой не меньше THRESHOLD:
 *
 *   название совпало «по сути» (без слов «клиника», «центр» и т. п.)  — 60
 *   название содержится одно в другом / один и тот же набор слов      — 45
 *   название похоже с опечаткой                                       — 35
 *   телефон совпал (последние 10 цифр)                                — 50
 *   улица и дом совпали                                               — 35 (только улица — 8)
 *   тот же город                                                      — 10
 *   то же направление/день рождения у людей                           — до 25
 *
 * Примеры: одно и то же название в том же городе — 70 (дубль); только совпавший адрес — 45 (не дубль:
 * в одном здании бывают разные организации); совпал телефон — 50 (дубль).
 */
class DuplicateFinder
{
    public const THRESHOLD = 50;
    private const LIMIT    = 3;

    /** Слова, которые ничего не говорят о названии организации. */
    private const ORG_GENERIC = [
        'ветеринарная', 'ветеринарный', 'ветеринарное', 'ветеринарии', 'ветеринарный', 'ветклиника', 'ветцентр',
        'клиника', 'клиники', 'центр', 'салон', 'студия', 'ооо', 'ип', 'ао', 'пао', 'зао', 'и', 'для', 'животных',
        'груминг', 'зоосалон', 'зоомагазин', 'магазин', 'аптека', 'ветаптека',
    ];

    /** Слова-типы в названиях улиц и населённых пунктов. */
    private const STREET_GENERIC = [
        'улица', 'ул', 'проспект', 'пр', 'пркт', 'ртк', 'переулок', 'пер', 'бульвар', 'бул', 'шоссе', 'ш',
        'площадь', 'пл', 'набережная', 'наб', 'проезд', 'пр-д', 'тупик', 'аллея', 'микрорайон', 'мкр',
    ];

    private const CITY_GENERIC = [
        'станица', 'ст', 'стца', 'посёлок', 'поселок', 'пос', 'п', 'село', 'с', 'хутор', 'х', 'деревня', 'д',
        'аул', 'город', 'г', 'пгт', 'рп', 'городской', 'округ',
    ];

    /* ========================================================================
     *  Публичные методы
     * ====================================================================== */

    /** Организация или клиника: ищем среди обеих таблиц. */
    public function forOrganization(array $in): array
    {
        return $this->safely(function () use ($in) {
            $cityName = $this->cityNameFromInput($in);
            $phones   = $this->phones([$in['phone1'] ?? null, $in['phone2'] ?? null, $in['phone'] ?? null]);

            $matches = [];

            foreach ([['clinic', Clinic::class], ['organization', Organization::class]] as [$type, $model]) {
                $rows = $this->orgCandidates($model, $cityName, $phones);

                foreach ($rows as $row) {
                    $score = $this->scoreOrganization($in, $cityName, $phones, $row);

                    if ($score >= self::THRESHOLD) {
                        $matches[] = $this->describeOrg($type, $row, $score);
                    }
                }
            }

            return $this->top($matches);
        });
    }

    /** Врач или специалист: ищем среди обеих таблиц. */
    public function forPerson(array $in): array
    {
        return $this->safely(function () use ($in) {
            $cityName = $this->cityNameFromInput($in);
            $cityId   = isset($in['city_id']) && $in['city_id'] !== '' ? (int) $in['city_id'] : null;
            $phones   = $this->phones([$in['phone'] ?? null]);

            $matches = [];

            foreach ([['doctor', Doctor::class], ['specialist', Specialist::class]] as [$type, $model]) {
                foreach ($this->personCandidates($model, $cityId, $cityName, $phones) as $row) {
                    $score = $this->scorePerson($in, $cityId, $cityName, $phones, $row);

                    if ($score >= self::THRESHOLD) {
                        $matches[] = $this->describePerson($type, $row, $score);
                    }
                }
            }

            return $this->top($matches);
        });
    }

    /**
     * Похожие населённые пункты в регионе: «Динская» ~ «Станица Динская».
     * Вызывается до создания города; точное совпадение названия сюда не попадает —
     * такой город CityResolver просто находит.
     */
    public function forCity(string $name, string $region): array
    {
        return $this->safely(function () use ($name, $region) {
            $typedCore = $this->cityCore($name);

            if ($typedCore === '' || trim($region) === '') {
                return [];
            }

            $typedFull = FuzzySearch::normalize($name);
            $matches   = [];

            $cities = City::query()
                ->whereRaw('LOWER(TRIM(region)) = ?', [mb_strtolower(trim($region))])
                ->get(['id', 'name', 'region']);

            foreach ($cities as $city) {
                if (FuzzySearch::normalize($city->name) === $typedFull) {
                    continue; // это тот же город — дубль создавать не придётся
                }

                $core = $this->cityCore($city->name);
                $k    = max(0, FuzzySearch::maxDistance(mb_strlen($typedCore)));

                $similar = $core === $typedCore
                    || (mb_strlen($typedCore) >= 4 && (str_contains($core, $typedCore) || str_contains($typedCore, $core)) && mb_strlen($core) >= 4)
                    || ($k > 0 && FuzzySearch::distance($typedCore, $core) <= $k && abs(mb_strlen($core) - mb_strlen($typedCore)) <= $k);

                if ($similar) {
                    $matches[] = [
                        'type'    => 'city',
                        'label'   => 'населённый пункт',
                        'adj'     => 'похожий',
                        'pron'    => 'он',
                        'id'      => $city->id,
                        'name'    => $city->name,
                        'address' => $city->region,
                        'phone'   => null,
                        'url'     => null,
                        'score'   => $core === $typedCore ? 100 : 70,
                    ];
                }
            }

            return $this->top($matches);
        });
    }

    /** Ответ 409: «найдено похожее» — фронт показывает окно «Это оно?». */
    public static function conflict(array $matches): JsonResponse
    {
        $first = $matches[0];

        return response()->json([
            'success'    => false,
            'duplicates' => $matches,
            'message'    => 'Мы нашли ' . ($first['adj'] ?? 'похожую') . " {$first['label']}. Это " . ($first['pron'] ?? 'она') . '?',
        ], 409);
    }

    /** Название города из запроса: выбранного из списка или введённого вручную. */
    public function cityNameFromRequest(Request $request): array
    {
        return [
            'city_id'   => $request->input('city_id'),
            'city_name' => $request->input('city_name'),
            'region'    => $request->input('region'),
        ];
    }

    /* ========================================================================
     *  Кандидаты из базы
     * ====================================================================== */

    private function orgCandidates(string $model, ?string $cityName, array $phones)
    {
        $query = $model::query();

        $query->where(function ($q) use ($cityName, $phones) {
            $has = false;

            if ($cityName) {
                // ядро названия города ловит варианты: «Станица Динская», «ст. Динская», «Динская»
                $core = $this->cityCore($cityName);
                $q->where('city', 'LIKE', '%' . $this->escapeLike($core ?: $cityName) . '%');
                $has = true;
            }

            foreach ($phones as $phone) {
                foreach (['phone1', 'phone2'] as $column) {
                    $q->orWhereRaw($this->phoneDigitsSql($column) . ' LIKE ?', ['%' . $phone]);
                }
                $has = true;
            }

            if (! $has) {
                $q->whereRaw('1 = 0');
            }
        });

        return $query->limit(300)->get();
    }

    private function personCandidates(string $model, ?int $cityId, ?string $cityName, array $phones)
    {
        $query = $model::query()->with('contacts');

        $query->where(function ($q) use ($cityId, $phones) {
            $has = false;

            if ($cityId) {
                $q->where('city_id', $cityId);
                $has = true;
            }

            foreach ($phones as $phone) {
                $q->orWhereHas('contacts', fn ($c) => $c->whereRaw($this->phoneDigitsSql('phone') . ' LIKE ?', ['%' . $phone]));
                $has = true;
            }

            if (! $has) {
                $q->whereRaw('1 = 0');
            }
        });

        return $query->limit(300)->get();
    }

    /* ========================================================================
     *  Оценка совпадения
     * ====================================================================== */

    private function scoreOrganization(array $in, ?string $cityName, array $phones, $row): int
    {
        $score = $this->nameScoreOrg((string) ($in['name'] ?? ''), (string) $row->name);

        // телефон
        if ($phones && array_intersect($phones, $this->phones([$row->phone1, $row->phone2]))) {
            $score += 50;
        }

        // адрес (улица + дом) — только в том же городе
        $sameCity = $cityName && $this->sameCity($cityName, (string) $row->city);
        if ($sameCity) {
            $score += 10 + $this->addressScore($in['street'] ?? null, $in['house'] ?? null, $row->street, $row->house);
        }

        return $score;
    }

    private function scorePerson(array $in, ?int $cityId, ?string $cityName, array $phones, $row): int
    {
        $nameScore = $this->nameScorePerson((string) ($in['name'] ?? ''), (string) $row->name);
        $score     = $nameScore;

        // телефон
        $rowPhones = $this->phones([$row->contacts?->phone ?? null]);
        if ($phones && $rowPhones && array_intersect($phones, $rowPhones)) {
            $score += 50;
        }

        // тот же город
        $sameCity = $cityId && (int) $row->city_id === $cityId;
        if ($sameCity) {
            $score += 10;
            // адрес частной практики (есть у специалистов)
            $score += $this->addressScore($in['street'] ?? null, $in['house'] ?? null, $row->street ?? null, $row->house ?? null);
        }

        // день рождения совпал — сильный признак одного человека (только вместе с похожим именем)
        if ($nameScore >= 35 && ! empty($in['date_of_birth']) && ! empty($row->date_of_birth)) {
            try {
                if (\Illuminate\Support\Carbon::parse($in['date_of_birth'])->isSameDay(\Illuminate\Support\Carbon::parse($row->date_of_birth))) {
                    $score += 25;
                }
            } catch (\Throwable $e) {
                // некорректная дата — пропускаем признак
            }
        }

        return $score;
    }

    /** Название организации. */
    private function nameScoreOrg(string $a, string $b): int
    {
        $ca = $this->orgCore($a);
        $cb = $this->orgCore($b);

        if ($ca === '' || $cb === '') {
            return 0;
        }

        // «Вет Макс» и «ВетМакс» — одно и то же: сравниваем и полные названия без пробелов/знаков
        if ($ca === $cb || implode('', $this->tokens($a)) === implode('', $this->tokens($b))) {
            return 60;
        }

        [$short, $long] = mb_strlen($ca) <= mb_strlen($cb) ? [$ca, $cb] : [$cb, $ca];

        if (mb_strlen($short) >= 5 && str_contains($long, $short)) {
            return 45;
        }

        $k = FuzzySearch::maxDistance(mb_strlen($short));

        if ($k > 0 && abs(mb_strlen($ca) - mb_strlen($cb)) <= $k && FuzzySearch::distance($short, $long) <= $k) {
            return 35;
        }

        return 0;
    }

    /** ФИО: порядок слов не важен («Иванов Иван» = «Иван Иванов»). */
    private function nameScorePerson(string $a, string $b): int
    {
        $ta = $this->tokens($a);
        $tb = $this->tokens($b);

        if (! $ta || ! $tb) {
            return 0;
        }

        sort($ta);
        sort($tb);

        $ja = implode('', $ta);
        $jb = implode('', $tb);

        if ($ja === $jb) {
            return 60;
        }

        // «Иванов Иван» ⊂ «Иванов Иван Иванович»
        [$short, $long] = count($ta) <= count($tb) ? [$ta, $tb] : [$tb, $ta];
        if (count($short) >= 2 && ! array_diff($short, $long)) {
            return 45;
        }

        $k = FuzzySearch::maxDistance(min(mb_strlen($ja), mb_strlen($jb)));

        if ($k > 0 && abs(mb_strlen($ja) - mb_strlen($jb)) <= $k && FuzzySearch::distance($ja, $jb) <= $k
            && FuzzySearch::distance($jb, $ja) <= $k) {
            return 35;
        }

        return 0;
    }

    /** Совпадение адреса: улица + дом = 35, только улица = 8. */
    private function addressScore(?string $street1, ?string $house1, ?string $street2, ?string $house2): int
    {
        $s1 = $this->streetCore((string) $street1);
        $s2 = $this->streetCore((string) $street2);

        if ($s1 === '' || $s2 === '') {
            return 0;
        }

        $k = FuzzySearch::maxDistance(mb_strlen($s1));
        $sameStreet = $s1 === $s2
            || ($k > 0 && abs(mb_strlen($s1) - mb_strlen($s2)) <= 1 && FuzzySearch::distance($s1, $s2) <= min($k, 1));

        if (! $sameStreet) {
            return 0;
        }

        $h1 = $this->house((string) $house1);
        $h2 = $this->house((string) $house2);

        return ($h1 !== '' && $h1 === $h2) ? 35 : 8;
    }

    /* ========================================================================
     *  Нормализация
     * ====================================================================== */

    private function tokens(string $text): array
    {
        $text = str_replace('ё', 'е', mb_strtolower($text));

        // «ст-ца» → «станица», «п.г.т.» → «пгт»: иначе дефис и точки разорвали бы слово на куски
        $text = preg_replace('/(?<![\p{L}])ст\s*-?\s*ца(?![\p{L}])/u', 'станица', $text) ?? $text;
        $text = preg_replace('/(?<![\p{L}])п\s*\.?\s*г\s*\.?\s*т(?![\p{L}])\.?/u', 'пгт', $text) ?? $text;

        return array_values(array_filter(preg_split('/[^\p{L}\p{N}]+/u', $text) ?: [], fn ($t) => $t !== ''));
    }

    /** Название организации без служебных слов, одной строкой. */
    private function orgCore(string $name): string
    {
        $tokens = $this->tokens($name);
        $core   = array_values(array_filter($tokens, fn ($t) => ! in_array($t, self::ORG_GENERIC, true)));

        return implode('', $core ?: $tokens);
    }

    private function streetCore(string $street): string
    {
        $tokens = $this->tokens($street);
        $core   = array_values(array_filter($tokens, fn ($t) => ! in_array($t, self::STREET_GENERIC, true)));

        return implode('', $core ?: $tokens);
    }

    /** Номер дома: «д. 42 А» → «42а», «42/1» → «421». */
    private function house(string $house): string
    {
        $house = mb_strtolower($house);
        $house = preg_replace('/\b(дом|д)\b\.?/u', '', $house) ?? $house;

        return preg_replace('/[^\p{L}\p{N}]+/u', '', str_replace('ё', 'е', $house)) ?? '';
    }

    /** Название города без «станица», «посёлок» и т. п. */
    public function cityCore(string $city): string
    {
        $tokens = $this->tokens($city);
        $core   = array_values(array_filter($tokens, fn ($t) => ! in_array($t, self::CITY_GENERIC, true)));

        return implode('', $core ?: $tokens);
    }

    private function sameCity(string $a, string $b): bool
    {
        return $this->cityCore($a) !== '' && $this->cityCore($a) === $this->cityCore($b);
    }

    /** Последние 10 цифр номера; короче 10 цифр — не учитываем (нельзя уверенно сравнивать). */
    private function phones(array $values): array
    {
        $result = [];

        foreach ($values as $value) {
            $digits = preg_replace('/\D+/', '', (string) $value) ?? '';

            if (strlen($digits) >= 10) {
                $result[] = substr($digits, -10);
            }
        }

        return array_values(array_unique($result));
    }

    /** SQL: колонка с телефоном без пробелов, дефисов и скобок — для сравнения по последним цифрам. */
    private function phoneDigitsSql(string $column): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE($column,' ',''),'-',''),'(',''),')',''),'+',''),'.','')";
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    /** Название города из введённых данных: по city_id или введённое вручную. */
    private function cityNameFromInput(array $in): ?string
    {
        if (! empty($in['city_name'])) {
            return trim((string) $in['city_name']);
        }

        if (! empty($in['city_id'])) {
            return City::whereKey($in['city_id'])->value('name');
        }

        return null;
    }

    /* ========================================================================
     *  Описание найденного
     * ====================================================================== */

    private function describeOrg(string $type, $row, int $score): array
    {
        $isClinic = $type === 'clinic';

        return [
            'type'    => $type,
            'label'   => $isClinic ? 'клинику' : 'организацию',
            'adj'     => 'похожую',
            'pron'    => 'она',
            'id'      => $row->id,
            'name'    => $row->name,
            'address' => trim(implode(', ', array_filter([$row->city, trim(($row->street ?? '') . ' ' . ($row->house ?? ''))]))),
            'phone'   => $row->phone1 ?: $row->phone2,
            'url'     => $this->safeUrl(fn () => $isClinic
                ? route('clinics.show', ['city' => $row->city_slug, 'clinic' => $row->slug])
                : route('organizations.show', ['city' => $row->city_slug, 'slug' => $row->slug])),
            'score'   => $score,
        ];
    }

    private function describePerson(string $type, $row, int $score): array
    {
        $isDoctor = $type === 'doctor';
        $city     = $row->city_id ? City::whereKey($row->city_id)->value('name') : null;

        return [
            'type'    => $type,
            'label'   => $isDoctor ? 'врача' : 'специалиста',
            'adj'     => 'похожего',
            'pron'    => 'он/она',
            'id'      => $row->id,
            'name'    => $row->name,
            'address' => trim(implode(', ', array_filter([$city, $row->specialization ?? null]))),
            'phone'   => $row->contacts?->phone,
            'url'     => $this->safeUrl(fn () => $row->slug
                ? route($isDoctor ? 'doctors.show' : 'specialists.show', $row->slug)
                : null),
            'score'   => $score,
        ];
    }

    private function top(array $matches): array
    {
        usort($matches, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($matches, 0, self::LIMIT);
    }

    private function safeUrl(\Closure $make): ?string
    {
        try {
            return $make();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Ошибка поиска дублей не должна мешать созданию записи. */
    private function safely(\Closure $callback): array
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            Log::warning('DuplicateFinder: ' . $e->getMessage());

            return [];
        }
    }
}
