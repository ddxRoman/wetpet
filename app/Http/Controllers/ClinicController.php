<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Clinic;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    /**
     * Список всех клиник с сортировкой по рейтингу
     */
public function index(Request $request)
{
    $user = auth()->user();

    // Определяем город (ваша текущая логика)
    if ($user && $user->city_id) {
        $city = City::find($user->city_id);
        $selectedCity = $city?->name;
    } else {
        $selectedCity = session('city_name');
    }

    // Модель выбранного города (нужен регион, чтобы показать «другие населённые пункты»)
    $cityModel = null;
    if ($selectedCity) {
        $sessionCity = session('city_id') ? City::find(session('city_id')) : null;
        $cityModel = ($sessionCity && mb_strtolower(trim($sessionCity->name)) === mb_strtolower(trim($selectedCity)))
            ? $sessionCity
            : City::whereRaw('LOWER(TRIM(name)) = LOWER(TRIM(?))', [$selectedCity])->first();
    }

    // Фильтр «Другие населённые пункты» (запоминается в сессии и cookie)
    $otherOnly = \App\Support\LocalityFilter::resolve($request);

    // Включаем пагинацию
    $clinics = Clinic::with(['promotions' => fn($q) => $q->active()])
        ->withAvg('reviews', 'rating')
        ->forCatalog($cityModel, $otherOnly)
        ->localFirst($cityModel)
        ->orderByDesc('reviews_avg_rating')
        ->paginate(16); // Было ->get()

    // SEO: редактируемый шаблон каталога клиник
    $seoManager = new \App\Services\SeoManager();
    $seoMeta = $seoManager->getCatalogMeta('clinics', ['city' => $selectedCity]);

// Если это AJAX (нажатие "Показать еще")
if ($request->ajax()) {
    // Возвращаем ту же вьюху index, JS сам вырежет из неё новые карточки и кнопку
    return view('pages.clinics.index', compact('clinics', 'selectedCity', 'seoMeta', 'otherOnly'));
}

    return view('pages.clinics.index', compact('clinics', 'selectedCity', 'seoMeta', 'otherOnly'));
}

    /**
     * Просмотр одной клиники
     */
    public function show(string $city, Clinic $clinic)
    {
        // Каноническая ссылка вида /clinics/{city}/{slug}: если сегмент города
        // в URL не совпадает с актуальным городом клиники (переехала, опечатка
        // в старой ссылке и т.п.) — редиректим на правильный адрес.
        if ($city !== $clinic->city_slug) {
            return redirect()->route('clinics.show', ['city' => $clinic->city_slug, 'clinic' => $clinic], 301);
        }

        $clinic->load(['awards', 'doctors']);

        $seoMeta = (new \App\Services\SeoManager())->getMeta($clinic);

        return view('pages.clinics.show', compact('clinic', 'seoMeta'));
    }

    /**
     * Форма добавления новой клиники
     */
    public function create()
    {
        return view('pages.clinics.create');
    }

    /**
     * Сохранение новой клиники
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'nullable|string|max:100',
            'city_id' => 'required|exists:cities,id',
            'street' => 'required|string|max:255',
            'house' => 'nullable|string|max:50',
            'address_comment' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
            'description' => 'nullable|string',
            'phone1' => 'nullable|string|max:30',
            'phone2' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'telegram' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'schedule' => 'nullable|string|max:100',
            'workdays' => 'nullable|string|max:100',
        ]);

        $city = City::findOrFail($data['city_id']);

        $clinic = Clinic::create([
            'name' => $data['name'],
            'country' => 'Россия',
            'region' => $data['region'] ?? null,
            'city' => $city->name,
            'street' => $data['street'],
            'house' => $data['house'] ?? null,
            'address_comment' => $data['address_comment'] ?? null,
            'description' => $data['description'] ?? null,
            'phone1' => $data['phone1'] ?? null,
            'phone2' => $data['phone2'] ?? null,
            'email' => $data['email'] ?? null,
            'schedule' => $data['schedule'] ?? null,
            'workdays' => $data['workdays'] ?? null,
        ]);

        // 🔔 TELEGRAM
        $user = auth()->user();
        app(TelegramService::class)->send(
            "🏥 <b>Новая клиника</b>\n\n" .
            "Название: {$clinic->name}\n" .
            "Город: {$clinic->city}\n" .
            "Адрес: {$clinic->street} {$clinic->house}\n\n" .
            "👤 <b>Добавил:</b>\n" .
            "Имя: " . ($user?->name ?? 'Гость') . "\n" .
            "Email: " . ($user?->email ?? '—') . "\n\n" .
            "🏷 <b>Пользователь добавил свою организацию</b>"
        );

        return redirect()
            ->route('clinics.show', ['city' => $clinic->city_slug, 'clinic' => $clinic])
            ->with('success', 'Клиника добавлена');
    }

    /**
     * API метод получения клиник по городу (тоже с сортировкой)
     */
    public function clinicsByCity($cityId)
    {
        $city = City::find($cityId);

        if (!$city) {
            return response()->json([]);
        }

        $clinics = Clinic::withAvg('reviews', 'rating')
            ->whereRaw(
                'LOWER(TRIM(city)) = LOWER(TRIM(?))',
                [$city->name]
            )
            ->orderByDesc('reviews_avg_rating')
            ->get();

        return response()->json($clinics);
    }

    /**
     * Форма редактирования
     */
    public function edit(Clinic $clinic)
    {
        return view('pages.clinics.edit', compact('clinic'));
    }

    /**
     * Живой поиск
     */
public function liveSearch(Request $request)
{
    $query = $request->get('q');
    if (mb_strlen($query) < 2) return response()->json(['results' => []]);

    // ── Вариант запроса "в другой раскладке" ──────────────────
    // Если человек набрал русское слово при включённой английской
    // раскладке (или наоборот), $queryAlt даёт правильный вариант.
    // Ищем ОБА варианта одновременно.
    $queryAlt = \App\Support\KeyboardLayout::swap($query);

    // ── Определение целевого города ──────────────────────────
    // 1. Если в самом запросе явно указан город ("Мопс Новосибирск") —
    //    вычленяем его название и убираем из текста поиска, чтобы оно
    //    не мешало поиску по имени/породе.
    // 2. Иначе используем текущий город пользователя (сессия, куда его
    //    кладёт DetectUserCity — ручной выбор, профиль или GeoIP).
    $queryLower = mb_strtolower($query);
    $matchedCityName = null;
    $matchedLength = 0;

    foreach (\App\Models\City::pluck('name') as $cityName) {
        $cityNameLower = mb_strtolower(trim($cityName));
        if ($cityNameLower !== '' && mb_stripos($queryLower, $cityNameLower) !== false) {
            if (mb_strlen($cityNameLower) > $matchedLength) {
                $matchedCityName = $cityName;
                $matchedLength = mb_strlen($cityNameLower);
            }
        }
    }

    $searchTerm = $query;
    if ($matchedCityName) {
        $stripped = trim(preg_replace('/' . preg_quote($matchedCityName, '/') . '/iu', '', $query));
        // Если после вычитания города ничего не осталось (запрос был
        // просто названием города) — ищем по исходному запросу целиком.
        if (mb_strlen($stripped) >= 2) {
            $searchTerm = $stripped;
        }
    }

    // Раскладочный вариант поискового термина (после вычитания города)
    // Регионы, явно указанные в запросе («... Ростовская область») — вырезаем из текста поиска
    [$searchTerm, $namedRegions] = \App\Support\RegionMatcher::extract($searchTerm);
    $searchTermAlt = \App\Support\KeyboardLayout::swap($searchTerm);

    // Город, В КОТОРОМ ищем — теперь это ЖЁСТКИЙ фильтр, а не просто
    // приоритет сортировки: если город определён (явно в запросе или
    // текущий город пользователя), результаты из других городов вообще
    // не показываем. Породы животных к городу не привязаны — их фильтр
    // не касается.
    $targetCityName = $matchedCityName ?: session('city_name');
    $targetCityNameLower = $targetCityName ? mb_strtolower(trim($targetCityName)) : null;

    // Модель города, в котором ищем (нужен регион). Если города нет в cities — ищем по названию
    $targetCity = $targetCityName
        ? (\App\Models\City::whereRaw('LOWER(TRIM(name)) = ?', [$targetCityNameLower])->first()
            ?? new \App\Models\City(['name' => $targetCityName]))
        : null;

    // Свой регион в «другие регионы» не входит
    $namedRegions = array_values(array_filter($namedRegions, fn ($r) => ! $targetCity
        || mb_strtolower(trim($r)) !== mb_strtolower(trim((string) $targetCity->region))));

    // Ярус выдачи: 0 — указанные в запросе регионы, 1 — выбранный город и не-географичные записи,
    // 2 — «другие населённые пункты» региона
    $tierOf = function (?string $city, ?string $region) use ($targetCityNameLower, $namedRegions) {
        $cityL = mb_strtolower(trim((string) $city));
        $regionL = mb_strtolower(trim((string) $region));
        foreach ($namedRegions as $r) {
            if ($regionL === mb_strtolower(trim($r))) return 0;
        }
        return ($targetCityNameLower && $cityL !== $targetCityNameLower) ? 2 : 1;
    };

    // Разбиваем запрос на отдельные слова для гибкого поиска
    $words = explode(' ', $searchTerm);
    $wordsAlt = explode(' ', $searchTermAlt);

    // Вспомогательная функция для расширенного поиска (Название + Адрес).
    // Для каждого слова проверяем и обычный вариант, и вариант в другой
    // раскладке — при этом каждое СЛОВО должно совпасть хотя бы в одном
    // из вариантов (а между словами — обычное И, как и раньше).
    // Используется в Клиниках и Организациях
    $applyAdvancedSearch = function($q) use ($words, $wordsAlt) {
        foreach ($words as $i => $word) {
            $wordAlt = $wordsAlt[$i] ?? $word;
            $q->where(function($sub) use ($word, $wordAlt) {
                $sub->where('name', 'LIKE', "%{$word}%")
                    ->orWhere('street', 'LIKE', "%{$word}%")
                    ->orWhere('city', 'LIKE', "%{$word}%")
                    ->orWhere('house', 'LIKE', "%{$word}%");
                if ($wordAlt !== $word) {
                    $sub->orWhere('name', 'LIKE', "%{$wordAlt}%")
                        ->orWhere('street', 'LIKE', "%{$wordAlt}%")
                        ->orWhere('city', 'LIKE', "%{$wordAlt}%")
                        ->orWhere('house', 'LIKE', "%{$wordAlt}%");
                }
            });
        }
    };

    // Жёсткий фильтр по городу для клиник/организаций (строковое поле city)
    $applyCityFilter = function($q) use ($targetCityNameLower) {
        if ($targetCityNameLower) {
            $q->whereRaw('LOWER(city) = ?', [$targetCityNameLower]);
        }
    };

    // Определяет "силу" совпадения, чтобы прямые вхождения (точное
    // совпадение / совпадение с начала слова) шли раньше, чем те,
    // где запрос найден только как часть названия или в доп.полях
    // (адрес, специализация и т.д.). Проверяем оба варианта раскладки
    // и берём лучший (меньший) результат.
    // 0 — точное совпадение, 1 — совпадение с начала, 2 — вхождение
    // в основное поле, 3 — совпадение только по доп.полям
    $matchPriority = function (string $primary, array $altFields = []) use ($searchTerm, $searchTermAlt) {
        $score = function (string $qNorm) use ($primary, $altFields) {
            $qNorm = mb_strtolower(trim($qNorm));
            $pNorm = mb_strtolower(trim($primary));

            if ($qNorm !== '' && $pNorm === $qNorm) {
                return 0;
            }
            if ($qNorm !== '' && mb_strpos($pNorm, $qNorm) === 0) {
                return 1;
            }
            if ($qNorm !== '' && mb_stripos($pNorm, $qNorm) !== false) {
                return 2;
            }
            foreach ($altFields as $alt) {
                if ($alt && mb_stripos(mb_strtolower($alt), $qNorm) !== false) {
                    return 3;
                }
            }
            return 4;
        };

        return min($score($searchTerm), $score($searchTermAlt));
    };

    $results = collect();

    // 1. Клиники
    \App\Models\Clinic::where(function($q) use ($applyAdvancedSearch) {
            $applyAdvancedSearch($q);
        })
        ->forSearch($targetCity, $namedRegions)
            ->searchRank($targetCity, $namedRegions)
        ->limit(10)->get()->each(function($item) use (&$results, $matchPriority, $tierOf) {
            $results->push([
                'type' => 'clinic',
                'other_locality' => $item->is_other_locality,
                '_tier' => $tierOf($item->city, $item->region),
                '_entity_id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'city_slug' => $item->city_slug,
                'address' => "{$item->city}, {$item->street} {$item->house}",
                'image' => $item->logo ? \Storage::url($item->logo) : asset('storage/clinics/logo/default-clinic.webp'),
                '_priority' => $matchPriority($item->name, [$item->street, $item->city, $item->house]),
                '_type_order' => 0,
            ]);
        });

    // 2. Врачи
    \App\Models\Doctor::with(['clinic', 'city'])
        ->where(function($q) use ($searchTerm, $searchTermAlt) {
            $q->where('name', 'LIKE', "%{$searchTerm}%")
              ->orWhere('specialization', 'LIKE', "%{$searchTerm}%");
            if ($searchTermAlt !== $searchTerm) {
                $q->orWhere('name', 'LIKE', "%{$searchTermAlt}%")
                  ->orWhere('specialization', 'LIKE', "%{$searchTermAlt}%");
            }
        })
        ->when($targetCityNameLower, function ($q) use ($targetCityNameLower, $targetCity, $namedRegions) {
            $q->where(function ($inner) use ($targetCityNameLower, $targetCity, $namedRegions) {
                $inner->whereHas('city', fn($c) => $c->whereRaw('LOWER(name) = ?', [$targetCityNameLower]))
                    ->orWhereHas('clinic', fn($c) => $c->forSearch($targetCity, $namedRegions))
                    ->orWhere('works_online', true)
                    ->orWhere(function ($none) {
                        $none->whereNull('city_id')->whereNull('clinic_id');
                    });
            });
        })
        ->limit(10)->get()->each(function($item) use (&$results, $matchPriority) {
            $clinicAddress = $item->clinic 
                ? " ({$item->clinic->city}, {$item->clinic->street} {$item->clinic->house})" 
                : "";
            $results->push([
                'type' => 'doctor',
                '_entity_id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'specialization' => $item->specialization,
                'clinic_info' => ($item->clinic->name ?? 'Частная практика') . $clinicAddress,
                'image' => $item->photo ? \Storage::url($item->photo) : asset('storage/doctors/default-doctor.webp'),
                '_priority' => $matchPriority($item->name, [$item->specialization]),
                '_type_order' => 2,
            ]);
        });

    // 3. Организации
    \App\Models\Organization::with(['fieldOfActivity'])
        ->where(function($q) use ($applyAdvancedSearch) {
            $applyAdvancedSearch($q);
        })
        ->forSearch($targetCity, $namedRegions)
            ->searchRank($targetCity, $namedRegions)
        ->limit(10)->get()->each(function($item) use (&$results, $matchPriority, $tierOf) {
            $results->push([
                'type' => 'organization',
                'other_locality' => $item->is_other_locality,
                '_tier' => $tierOf($item->city, $item->region),
                '_entity_id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'city_slug' => $item->city_slug,
                'category_name' => $item->fieldOfActivity->name ?? '', 
                'address' => "{$item->city}, {$item->street} {$item->house}",
                'image' => $item->logo ? \Storage::url($item->logo) : asset('storage/organizations/default-org.webp'),
                '_priority' => $matchPriority($item->name, [$item->street, $item->city, $item->house]),
                '_type_order' => 1,
            ]);
        });

    // 4. Специалисты
    \App\Models\Specialist::with(['organization', 'city'])
        ->where(function($q) use ($searchTerm, $searchTermAlt) {
            $q->where('name', 'LIKE', "%{$searchTerm}%")
              ->orWhere('specialization', 'LIKE', "%{$searchTerm}%");
            if ($searchTermAlt !== $searchTerm) {
                $q->orWhere('name', 'LIKE', "%{$searchTermAlt}%")
                  ->orWhere('specialization', 'LIKE', "%{$searchTermAlt}%");
            }
        })
        ->when($targetCityNameLower, function ($q) use ($targetCityNameLower, $targetCity, $namedRegions) {
            $q->where(function ($inner) use ($targetCityNameLower, $targetCity, $namedRegions) {
                $inner->whereHas('city', fn($c) => $c->whereRaw('LOWER(name) = ?', [$targetCityNameLower]))
                    ->orWhereHas('organization', fn($c) => $c->forSearch($targetCity, $namedRegions))
                    ->orWhere('works_online', true)
                    ->orWhere(function ($none) {
                        $none->whereNull('city_id')->whereNull('organization_id');
                    });
            });
        })
        ->limit(10)->get()->each(function($item) use (&$results, $matchPriority) {
            if ($item->organization) {
                $location = "{$item->organization->name} ({$item->organization->city}, {$item->organization->street} {$item->organization->house})";
            } else {
                $cityName = $item->city->name ?? 'Город не указан'; 
                $location = "Частный специалист: {$cityName}, {$item->street} {$item->house}";
            }
            $results->push([
                'type' => 'specialist',
                '_entity_id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'specialization' => $item->specialization,
                'location_info' => $location,
                'image' => $item->photo ? \Storage::url($item->photo) : asset('storage/doctors/default-doctor.webp'),
                '_priority' => $matchPriority($item->name, [$item->specialization]),
                '_type_order' => 3,
            ]);
        });

    // 5. Животные (породы) — не привязаны к городу, фильтр по городу
    // на них не действует
    \App\Models\Animal::with('details')
        ->where(function($q) use ($searchTerm, $searchTermAlt) {
            // Поиск по породе или виду
            $q->where('breed', 'LIKE', "%{$searchTerm}%")
              ->orWhere('species', 'LIKE', "%{$searchTerm}%")
              ->orWhereRaw("CONCAT(species, ' ', breed) LIKE ?", ["%{$searchTerm}%"]);
            if ($searchTermAlt !== $searchTerm) {
                $q->orWhere('breed', 'LIKE', "%{$searchTermAlt}%")
                  ->orWhere('species', 'LIKE', "%{$searchTermAlt}%")
                  ->orWhereRaw("CONCAT(species, ' ', breed) LIKE ?", ["%{$searchTermAlt}%"]);
            }
        })
        ->limit(5)->get()->each(function($item) use (&$results, $matchPriority) {
            $results->push([
                'type' => 'animal',
                'name' => $item->breed,
                'slug' => $item->breed_slug,
                'species_slug' => $item->species_slug, 
                'category' => $item->species,
                'image' => ($item->details->photo ?? null) ? \Storage::url($item->details->photo) : asset('storage/animals/default-animal.webp'),
                '_priority' => $matchPriority($item->breed, [$item->species]),
                '_type_order' => 4,
            ]);
        });

    // 6. Поиск по названию услуги: организации/клиники/врачи/специалисты,
    // которые её оказывают — сразу с ценой. Если сущность уже попала в
    // выдачу по названию/адресу выше — не дублируем карточку, а просто
    // дописываем в неё услугу и цену.
    $matchingServiceIds = \App\Models\Service::where(function ($q) use ($searchTerm, $searchTermAlt) {
            $q->where('name', 'LIKE', "%{$searchTerm}%");
            if ($searchTermAlt !== $searchTerm) {
                $q->orWhere('name', 'LIKE', "%{$searchTermAlt}%");
            }
        })
        ->pluck('id');

    if ($matchingServiceIds->isNotEmpty()) {
        $pricesByEntity = \App\Models\Price::whereIn('service_id', $matchingServiceIds)
            ->with('service')
            ->get()
            ->groupBy(fn ($p) => $p->priceable_type . '#' . $p->priceable_id);

        // Форматирование цены без лишних нулей: 1500 или 1500.5
        $formatPrice = function ($price, $currency) {
            $num = rtrim(rtrim(number_format((float) $price, 2, '.', ' '), '0'), '.');
            return $num . ' ' . ($currency ?: 'руб.');
        };

        foreach ($pricesByEntity as $pricesForEntity) {
            $cheapest = $pricesForEntity->sortBy('price')->first();
            $entityClass = $cheapest->priceable_type;
            $entityId = $cheapest->priceable_id;
            $serviceName = $cheapest->service->name ?? $query;
            $servicePrice = $formatPrice($cheapest->price, $cheapest->currency);

            // Уже есть в выдаче (нашлось по названию/адресу) — просто дописываем услугу и цену
            $alreadyFound = false;
            $results = $results->map(function ($r) use ($entityClass, $entityId, $serviceName, $servicePrice, &$alreadyFound) {
                $typeMap = [
                    \App\Models\Clinic::class => 'clinic',
                    \App\Models\Organization::class => 'organization',
                    \App\Models\Doctor::class => 'doctor',
                    \App\Models\Specialist::class => 'specialist',
                ];
                if (($r['type'] ?? null) === ($typeMap[$entityClass] ?? null) && ($r['_entity_id'] ?? null) === $entityId) {
                    $r['service_name'] = $serviceName;
                    $r['service_price'] = $servicePrice;
                    $alreadyFound = true;
                }
                return $r;
            });
            if ($alreadyFound) {
                continue;
            }

            if ($entityClass === \App\Models\Clinic::class) {
                $entity = \App\Models\Clinic::find($entityId);
                if (!$entity) continue;
                if ($targetCityNameLower && mb_strtolower(trim($entity->city)) !== $targetCityNameLower) continue;
                $results->push([
                    'type' => 'clinic',
                    'other_locality' => false,
                    '_tier' => 1,
                    '_entity_id' => $entity->id,
                    'name' => $entity->name,
                    'slug' => $entity->slug,
                    'city_slug' => $entity->city_slug,
                    'address' => "{$entity->city}, {$entity->street} {$entity->house}",
                    'image' => $entity->logo ? \Storage::url($entity->logo) : asset('storage/clinics/logo/default-clinic.webp'),
                    'service_name' => $serviceName,
                    'service_price' => $servicePrice,
                    '_priority' => $matchPriority($serviceName),
                    '_type_order' => 0,
                ]);
            } elseif ($entityClass === \App\Models\Organization::class) {
                $entity = \App\Models\Organization::with('fieldOfActivity')->find($entityId);
                if (!$entity) continue;
                if ($targetCityNameLower && mb_strtolower(trim($entity->city)) !== $targetCityNameLower) continue;
                $results->push([
                    'type' => 'organization',
                    'other_locality' => false,
                    '_tier' => 1,
                    '_entity_id' => $entity->id,
                    'name' => $entity->name,
                    'slug' => $entity->slug,
                    'city_slug' => $entity->city_slug,
                    'category_name' => $entity->fieldOfActivity->name ?? '',
                    'address' => "{$entity->city}, {$entity->street} {$entity->house}",
                    'image' => $entity->logo ? \Storage::url($entity->logo) : asset('storage/organizations/default-org.webp'),
                    'service_name' => $serviceName,
                    'service_price' => $servicePrice,
                    '_priority' => $matchPriority($serviceName),
                    '_type_order' => 1,
                ]);
            } elseif ($entityClass === \App\Models\Doctor::class) {
                $entity = \App\Models\Doctor::with(['clinic', 'city'])->find($entityId);
                if (!$entity) continue;
                $worksInTargetCity = $targetCityNameLower && (
                    mb_strtolower(trim($entity->city->name ?? '')) === $targetCityNameLower
                    || ($entity->clinic && mb_strtolower(trim($entity->clinic->city)) === $targetCityNameLower)
                );
                if ($targetCityNameLower && !$worksInTargetCity && !$entity->works_online) continue;
                $clinicAddress = $entity->clinic
                    ? " ({$entity->clinic->city}, {$entity->clinic->street} {$entity->clinic->house})"
                    : '';
                $results->push([
                    'type' => 'doctor',
                    '_tier' => 1,
                    '_entity_id' => $entity->id,
                    'name' => $entity->name,
                    'slug' => $entity->slug,
                    'specialization' => $entity->specialization,
                    'clinic_info' => ($entity->clinic->name ?? 'Частная практика') . $clinicAddress,
                    'image' => $entity->photo ? \Storage::url($entity->photo) : asset('storage/doctors/default-doctor.webp'),
                    'service_name' => $serviceName,
                    'service_price' => $servicePrice,
                    '_priority' => $matchPriority($serviceName),
                    '_type_order' => 2,
                ]);
            } elseif ($entityClass === \App\Models\Specialist::class) {
                $entity = \App\Models\Specialist::with(['organization', 'city'])->find($entityId);
                if (!$entity) continue;
                $worksInTargetCity = $targetCityNameLower && (
                    mb_strtolower(trim($entity->city->name ?? '')) === $targetCityNameLower
                    || ($entity->organization && mb_strtolower(trim($entity->organization->city)) === $targetCityNameLower)
                );
                if ($targetCityNameLower && !$worksInTargetCity && !$entity->works_online) continue;
                if ($entity->organization) {
                    $location = "{$entity->organization->name} ({$entity->organization->city}, {$entity->organization->street} {$entity->organization->house})";
                } else {
                    $cityName = $entity->city->name ?? 'Город не указан';
                    $location = "Частный специалист: {$cityName}, {$entity->street} {$entity->house}";
                }
                $results->push([
                    'type' => 'specialist',
                    '_tier' => 1,
                    '_entity_id' => $entity->id,
                    'name' => $entity->name,
                    'slug' => $entity->slug,
                    'specialization' => $entity->specialization,
                    'location_info' => $location,
                    'image' => $entity->photo ? \Storage::url($entity->photo) : asset('storage/doctors/default-doctor.webp'),
                    'service_name' => $serviceName,
                    'service_price' => $servicePrice,
                    '_priority' => $matchPriority($serviceName),
                    '_type_order' => 3,
                ]);
            }
        }
    }

    // Теперь город больше не влияет на сортировку (он уже жёсткий фильтр
    // выше) — сортируем только по релевантности текста и порядку типов.
    $sorted = $results
        ->map(fn ($i) => $i + ['_tier' => 1])
        ->sortBy(['_tier', '_priority', '_type_order'])
        ->values()
        ->take(20)
        ->map(function ($item) {
            unset($item['_priority'], $item['_type_order'], $item['_tier'], $item['_entity_id']);
            return $item;
        });

    return response()->json(['results' => $sorted]);
}

public function fullSearch(Request $request)
{
    $query = $request->get('q');
    if (!$query) return redirect()->back();

    // Вариант запроса в другой раскладке (см. liveSearch выше) — ищем
    // одновременно и обычный текст, и его "перевёрнутый" вариант.
    $queryAlt = \App\Support\KeyboardLayout::swap($query);

    // Город, явно упомянутый в запросе ("Мопс Краснодар"), либо —
    // если в запросе города нет — текущий город пользователя из сессии.
    // Если город определён, он становится жёстким фильтром: результаты
    // из других городов не показываются (кроме пород животных — они
    // к городу не привязаны).
    $queryLower = mb_strtolower($query);
    $matchedCityName = null;
    $matchedLength = 0;

    foreach (\App\Models\City::pluck('name') as $cityName) {
        $cityNameLower = mb_strtolower(trim($cityName));
        if ($cityNameLower !== '' && mb_stripos($queryLower, $cityNameLower) !== false) {
            if (mb_strlen($cityNameLower) > $matchedLength) {
                $matchedCityName = $cityName;
                $matchedLength = mb_strlen($cityNameLower);
            }
        }
    }

    $searchTerm = $query;
    if ($matchedCityName) {
        $stripped = trim(preg_replace('/' . preg_quote($matchedCityName, '/') . '/iu', '', $query));
        if (mb_strlen($stripped) >= 2) {
            $searchTerm = $stripped;
        }
    }
    // Регионы, явно указанные в запросе («... Ростовская область») — вырезаем из текста поиска
    [$searchTerm, $namedRegions] = \App\Support\RegionMatcher::extract($searchTerm);
    $searchTermAlt = \App\Support\KeyboardLayout::swap($searchTerm);

    $targetCityName = $matchedCityName ?: session('city_name');
    $targetCityNameLower = $targetCityName ? mb_strtolower(trim($targetCityName)) : null;

    // Модель города, в котором ищем (нужен регион). Если города нет в cities — ищем по названию
    $targetCity = $targetCityName
        ? (\App\Models\City::whereRaw('LOWER(TRIM(name)) = ?', [$targetCityNameLower])->first()
            ?? new \App\Models\City(['name' => $targetCityName]))
        : null;

    // Свой регион в «другие регионы» не входит
    $namedRegions = array_values(array_filter($namedRegions, fn ($r) => ! $targetCity
        || mb_strtolower(trim($r)) !== mb_strtolower(trim((string) $targetCity->region))));

    // Ярус выдачи: 0 — указанные в запросе регионы, 1 — выбранный город и не-географичные записи,
    // 2 — «другие населённые пункты» региона
    $tierOf = function (?string $city, ?string $region) use ($targetCityNameLower, $namedRegions) {
        $cityL = mb_strtolower(trim((string) $city));
        $regionL = mb_strtolower(trim((string) $region));
        foreach ($namedRegions as $r) {
            if ($regionL === mb_strtolower(trim($r))) return 0;
        }
        return ($targetCityNameLower && $cityL !== $targetCityNameLower) ? 2 : 1;
    };

    $words = explode(' ', $searchTerm);
    $wordsAlt = explode(' ', $searchTermAlt);

    // Универсальная функция поиска по адресу/названию (обычная раскладка + альтернативная)
    $applyAdvancedSearch = function($q) use ($words, $wordsAlt) {
        foreach ($words as $i => $word) {
            $wordAlt = $wordsAlt[$i] ?? $word;
            $q->where(function($sub) use ($word, $wordAlt) {
                $sub->where('name', 'LIKE', "%{$word}%")
                    ->orWhere('street', 'LIKE', "%{$word}%")
                    ->orWhere('city', 'LIKE', "%{$word}%")
                    ->orWhere('house', 'LIKE', "%{$word}%");
                if ($wordAlt !== $word) {
                    $sub->orWhere('name', 'LIKE', "%{$wordAlt}%")
                        ->orWhere('street', 'LIKE', "%{$wordAlt}%")
                        ->orWhere('city', 'LIKE', "%{$wordAlt}%")
                        ->orWhere('house', 'LIKE', "%{$wordAlt}%");
                }
            });
        }
    };

    // Жёсткий фильтр по городу для клиник/организаций (строковое поле city)
    $applyCityFilter = function($q) use ($targetCityNameLower) {
        if ($targetCityNameLower) {
            $q->whereRaw('LOWER(city) = ?', [$targetCityNameLower]);
        }
    };

    $results = [
        'clinics' => \App\Models\Clinic::where(function($q) use ($applyAdvancedSearch) {
                $applyAdvancedSearch($q);
            })
            ->forSearch($targetCity, $namedRegions)
            ->searchRank($targetCity, $namedRegions)
            ->get(),

        'organizations' => \App\Models\Organization::with('fieldOfActivity')
            ->where(function($q) use ($applyAdvancedSearch) {
                $applyAdvancedSearch($q);
            })
            ->forSearch($targetCity, $namedRegions)
            ->searchRank($targetCity, $namedRegions)
            ->get(),

        'doctors' => \App\Models\Doctor::with('clinic')
            ->where(function($q) use ($searchTerm, $searchTermAlt, $words, $wordsAlt) {
                // Ищем по имени врача целиком (обе раскладки)
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('specialization', 'LIKE', "%{$searchTerm}%");
                if ($searchTermAlt !== $searchTerm) {
                    $q->orWhere('name', 'LIKE', "%{$searchTermAlt}%")
                      ->orWhere('specialization', 'LIKE', "%{$searchTermAlt}%");
                }
                // ИЛИ по адресу клиники (разбивая на слова, обе раскладки)
                $q->orWhereHas('clinic', function($sub) use ($words, $wordsAlt) {
                    foreach ($words as $i => $word) {
                        $wordAlt = $wordsAlt[$i] ?? $word;
                        $sub->where(function($inner) use ($word, $wordAlt) {
                            $inner->where('city', 'LIKE', "%{$word}%")
                                  ->orWhere('street', 'LIKE', "%{$word}%");
                            if ($wordAlt !== $word) {
                                $inner->orWhere('city', 'LIKE', "%{$wordAlt}%")
                                      ->orWhere('street', 'LIKE', "%{$wordAlt}%");
                            }
                        });
                    }
                });
            })
            ->when($targetCityNameLower, function ($q) use ($targetCityNameLower, $targetCity, $namedRegions) {
                $q->where(function ($inner) use ($targetCityNameLower, $targetCity, $namedRegions) {
                    $inner->whereHas('city', fn($c) => $c->whereRaw('LOWER(name) = ?', [$targetCityNameLower]))
                        ->orWhereHas('clinic', fn($c) => $c->forSearch($targetCity, $namedRegions))
                        ->orWhere('works_online', true)
                        ->orWhere(function ($none) {
                            $none->whereNull('city_id')->whereNull('clinic_id');
                        });
                });
            })
            ->get(),

        'specialists' => \App\Models\Specialist::with(['organization', 'city'])
            ->where(function($q) use ($searchTerm, $searchTermAlt, $words, $wordsAlt) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('specialization', 'LIKE', "%{$searchTerm}%");
                if ($searchTermAlt !== $searchTerm) {
                    $q->orWhere('name', 'LIKE', "%{$searchTermAlt}%")
                      ->orWhere('specialization', 'LIKE', "%{$searchTermAlt}%");
                }
                $q->orWhereHas('organization', function($sub) use ($words, $wordsAlt) {
                    foreach ($words as $i => $word) {
                        $wordAlt = $wordsAlt[$i] ?? $word;
                        $sub->where(function($inner) use ($word, $wordAlt) {
                            $inner->where('city', 'LIKE', "%{$word}%")
                                  ->orWhere('street', 'LIKE', "%{$word}%");
                            if ($wordAlt !== $word) {
                                $inner->orWhere('city', 'LIKE', "%{$wordAlt}%")
                                      ->orWhere('street', 'LIKE', "%{$wordAlt}%");
                            }
                        });
                    }
                });
            })
            ->when($targetCityNameLower, function ($q) use ($targetCityNameLower, $targetCity, $namedRegions) {
                $q->where(function ($inner) use ($targetCityNameLower, $targetCity, $namedRegions) {
                    $inner->whereHas('city', fn($c) => $c->whereRaw('LOWER(name) = ?', [$targetCityNameLower]))
                        ->orWhereHas('organization', fn($c) => $c->forSearch($targetCity, $namedRegions))
                        ->orWhere('works_online', true)
                        ->orWhere(function ($none) {
                            $none->whereNull('city_id')->whereNull('organization_id');
                        });
                });
            })
            ->get(),

        // Породы животных не привязаны к городу — фильтр по городу их не касается
        'animals' => \App\Models\Animal::where('breed', 'LIKE', "%{$searchTerm}%")
            ->orWhere('species', 'LIKE', "%{$searchTerm}%")
            ->when($searchTermAlt !== $searchTerm, function ($q) use ($searchTermAlt) {
                $q->orWhere('breed', 'LIKE', "%{$searchTermAlt}%")
                  ->orWhere('species', 'LIKE', "%{$searchTermAlt}%");
            })
            ->get(),
    ];

    return view('pages.search.index', compact('results', 'query'));
}

    /**
     * Обновление клиники
     */
    public function update(Request $request, $id)
    {
        $clinic = Clinic::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'street' => 'required|string|max:255',
            'house' => 'nullable|string|max:50',
            'address_comment' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:webp|max:4096',
            'description' => 'nullable|string',
            'phone1' => 'nullable|string|max:30',
            'phone2' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'telegram' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'schedule' => 'nullable|string|max:100',
            'workdays' => 'nullable|string|max:100',
            'seo_title' => 'nullable|string|max:255',
        'seo_description' => 'nullable|string',    
        ]);

        $clinic->update($data);

        return redirect()->route('clinics.show', ['city' => $clinic->city_slug, 'clinic' => $clinic])
                         ->with('success', 'Клиника обновлена');
    }

    /**
     * Удаление
     */
    public function destroy($id)
    {
        $clinic = Clinic::findOrFail($id);
        $clinic->delete();

        return redirect()->route('clinics.index')->with('success', 'Клиника удалена');
    }
}