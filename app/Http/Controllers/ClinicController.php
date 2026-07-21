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

    // Включаем пагинацию
    $clinics = Clinic::with(['promotions' => fn($q) => $q->active()])
        ->withAvg('reviews', 'rating')
        ->when($selectedCity, function ($query, $city) {
            $query->whereRaw(
                'LOWER(TRIM(city)) = LOWER(TRIM(?))',
                [$city]
            );
        })
        ->orderByDesc('reviews_avg_rating')
        ->paginate(16); // Было ->get()

// Если это AJAX (нажатие "Показать еще")
if ($request->ajax()) {
    // Возвращаем ту же вьюху index, JS сам вырежет из неё новые карточки и кнопку
    return view('pages.clinics.index', compact('clinics', 'selectedCity'));
}

    return view('pages.clinics.index', compact('clinics', 'selectedCity'));
}

    /**
     * Просмотр одной клиники
     */
    public function show(Clinic $clinic)
    {
        $clinic->load(['awards', 'doctors']);
        return view('pages.clinics.show', compact('clinic'));
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
            ->route('clinics.show', $clinic)
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

    $targetCityName = $matchedCityName ?: session('city_name');

    // Разбиваем запрос на отдельные слова для гибкого поиска
    $words = explode(' ', $searchTerm);

    // Вспомогательная функция для расширенного поиска (Название + Адрес)
    // Используется в Клиниках и Организациях
    $applyAdvancedSearch = function($q) use ($words) {
        foreach ($words as $word) {
            $q->where(function($sub) use ($word) {
                $sub->where('name', 'LIKE', "%{$word}%")
                    ->orWhere('street', 'LIKE', "%{$word}%")
                    ->orWhere('city', 'LIKE', "%{$word}%")
                    ->orWhere('house', 'LIKE', "%{$word}%");
            });
        }
    };

    // Тир по городу: 0 — совпадает с целевым городом (или у сущности
    // вообще нет привязки к городу, как у пород), 1 — другой город.
    // Используется как ГЛАВНЫЙ ключ сортировки, чтобы "свой" город
    // (или явно указанный в запросе) всегда шёл выше результатов
    // из других городов.
    $cityTier = function (?string $itemCityName) use ($targetCityName) {
        if (!$targetCityName || !$itemCityName) {
            return 0;
        }
        return mb_strtolower(trim($itemCityName)) === mb_strtolower(trim($targetCityName)) ? 0 : 1;
    };

    // Определяет "силу" совпадения, чтобы прямые вхождения (точное
    // совпадение / совпадение с начала слова) шли раньше, чем те,
    // где запрос найден только как часть названия или в доп.полях
    // (адрес, специализация и т.д.)
    // 0 — точное совпадение, 1 — совпадение с начала, 2 — вхождение
    // в основное поле, 3 — совпадение только по доп.полям
    $matchPriority = function (string $primary, array $altFields = []) use ($searchTerm) {
        $qNorm = mb_strtolower(trim($searchTerm));
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

    $results = collect();

    // 1. Клиники
    \App\Models\Clinic::where(function($q) use ($applyAdvancedSearch) {
            $applyAdvancedSearch($q);
        })
        ->limit(10)->get()->each(function($item) use (&$results, $matchPriority, $cityTier) {
            $results->push([
                'type' => 'clinic',
                'name' => $item->name,
                'slug' => $item->slug,
                'address' => "{$item->city}, {$item->street} {$item->house}",
                'image' => $item->logo ? \Storage::url($item->logo) : asset('storage/clinics/logo/default-clinic.webp'),
                '_priority' => $matchPriority($item->name, [$item->street, $item->city, $item->house]),
                '_city_tier' => $cityTier($item->city),
                '_type_order' => 0,
            ]);
        });

    // 2. Врачи
    \App\Models\Doctor::with(['clinic', 'city'])
        ->where(function($q) use ($searchTerm) {
            $q->where('name', 'LIKE', "%{$searchTerm}%")
              ->orWhere('specialization', 'LIKE', "%{$searchTerm}%");
        })
        ->limit(10)->get()->each(function($item) use (&$results, $matchPriority, $cityTier) {
            $clinicAddress = $item->clinic 
                ? " ({$item->clinic->city}, {$item->clinic->street} {$item->clinic->house})" 
                : "";
            $doctorCityName = $item->city->name ?? $item->clinic?->city ?? null;
            $results->push([
                'type' => 'doctor',
                'name' => $item->name,
                'slug' => $item->slug,
                'specialization' => $item->specialization,
                'clinic_info' => ($item->clinic->name ?? 'Частная практика') . $clinicAddress,
                'image' => $item->photo ? \Storage::url($item->photo) : asset('storage/doctors/default-doctor.webp'),
                '_priority' => $matchPriority($item->name, [$item->specialization]),
                '_city_tier' => $cityTier($doctorCityName),
                '_type_order' => 2,
            ]);
        });

    // 3. Организации
    \App\Models\Organization::with(['fieldOfActivity'])
        ->where(function($q) use ($applyAdvancedSearch) {
            $applyAdvancedSearch($q);
        })
        ->limit(10)->get()->each(function($item) use (&$results, $matchPriority, $cityTier) {
            $results->push([
                'type' => 'organization',
                'name' => $item->name,
                'slug' => $item->slug,
                'category_name' => $item->fieldOfActivity->name ?? '', 
                'address' => "{$item->city}, {$item->street} {$item->house}",
                'image' => $item->logo ? \Storage::url($item->logo) : asset('storage/organizations/default-org.webp'),
                '_priority' => $matchPriority($item->name, [$item->street, $item->city, $item->house]),
                '_city_tier' => $cityTier($item->city),
                '_type_order' => 1,
            ]);
        });

    // 4. Специалисты
    \App\Models\Specialist::with(['organization', 'city'])
        ->where(function($q) use ($searchTerm) {
            $q->where('name', 'LIKE', "%{$searchTerm}%")
              ->orWhere('specialization', 'LIKE', "%{$searchTerm}%");
        })
        ->limit(10)->get()->each(function($item) use (&$results, $matchPriority, $cityTier) {
            if ($item->organization) {
                $location = "{$item->organization->name} ({$item->organization->city}, {$item->organization->street} {$item->organization->house})";
                $specialistCityName = $item->organization->city ?? null;
            } else {
                $cityName = $item->city->name ?? 'Город не указан'; 
                $location = "Частный специалист: {$cityName}, {$item->street} {$item->house}";
                $specialistCityName = $item->city->name ?? null;
            }
            $results->push([
                'type' => 'specialist',
                'name' => $item->name,
                'slug' => $item->slug,
                'specialization' => $item->specialization,
                'location_info' => $location,
                'image' => $item->photo ? \Storage::url($item->photo) : asset('storage/doctors/default-doctor.webp'),
                '_priority' => $matchPriority($item->name, [$item->specialization]),
                '_city_tier' => $cityTier($specialistCityName),
                '_type_order' => 3,
            ]);
        });

    // 5. Животные (породы) — не привязаны к городу, тир всегда нейтральный
    \App\Models\Animal::with('details')
        ->where(function($q) use ($searchTerm) {
            // Поиск по породе или виду
            $q->where('breed', 'LIKE', "%{$searchTerm}%")
              ->orWhere('species', 'LIKE', "%{$searchTerm}%")
              ->orWhereRaw("CONCAT(species, ' ', breed) LIKE ?", ["%{$searchTerm}%"]);
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
                '_city_tier' => 0,
                '_type_order' => 4,
            ]);
        });

    // Город — главный ключ сортировки (свой/указанный город всегда
    // выше, другие города — внизу), затем релевантность текста,
    // затем порядок типов (клиники/организации/врачи/специалисты/животные)
    $sorted = $results
        ->sortBy(['_city_tier', '_priority', '_type_order'])
        ->values()
        ->take(20)
        ->map(function ($item) {
            unset($item['_priority'], $item['_city_tier'], $item['_type_order']);
            return $item;
        });

    return response()->json(['results' => $sorted]);
}

public function fullSearch(Request $request)
{
    $query = $request->get('q');
    if (!$query) return redirect()->back();

    $words = explode(' ', $query);

    // Универсальная функция поиска по адресу/названию
    $applyAdvancedSearch = function($q) use ($words) {
        foreach ($words as $word) {
            $q->where(function($sub) use ($word) {
                $sub->where('name', 'LIKE', "%{$word}%")
                    ->orWhere('street', 'LIKE', "%{$word}%")
                    ->orWhere('city', 'LIKE', "%{$word}%")
                    ->orWhere('house', 'LIKE', "%{$word}%");
            });
        }
    };

    $results = [
        'clinics' => \App\Models\Clinic::where(function($q) use ($applyAdvancedSearch) {
            $applyAdvancedSearch($q);
        })->get(),

        'organizations' => \App\Models\Organization::with('fieldOfActivity')
            ->where(function($q) use ($applyAdvancedSearch) {
                $applyAdvancedSearch($q);
            })->get(),

        'doctors' => \App\Models\Doctor::with('clinic')
            ->where(function($q) use ($query, $words) {
                // Ищем по имени врача целиком
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('specialization', 'LIKE', "%{$query}%")
                  // ИЛИ по адресу клиники (разбивая на слова)
                  ->orWhereHas('clinic', function($sub) use ($words) {
                      foreach ($words as $word) {
                          $sub->where(function($inner) use ($word) {
                              $inner->where('city', 'LIKE', "%{$word}%")
                                    ->orWhere('street', 'LIKE', "%{$word}%");
                          });
                      }
                  });
            })->get(),

        'specialists' => \App\Models\Specialist::with(['organization', 'city'])
            ->where(function($q) use ($query, $words) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('specialization', 'LIKE', "%{$query}%")
                  ->orWhereHas('organization', function($sub) use ($words) {
                      foreach ($words as $word) {
                          $sub->where(function($inner) use ($word) {
                              $inner->where('city', 'LIKE', "%{$word}%")
                                    ->orWhere('street', 'LIKE', "%{$word}%");
                          });
                      }
                  });
            })->get(),

        'animals' => \App\Models\Animal::where('breed', 'LIKE', "%{$query}%")
            ->orWhere('species', 'LIKE', "%{$query}%")
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

        return redirect()->route('pages.clinics.show', $clinic->slug)
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