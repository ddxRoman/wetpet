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
    $clinics = Clinic::withAvg('reviews', 'rating')
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
    if (mb_strlen($query) < 2) return response()->json([]);

    // 1. Клиники
    $clinics = \App\Models\Clinic::where('name', 'LIKE', "%{$query}%")
        ->limit(5)->get()->map(function($item) {
            return [
                'type' => 'clinic',
                'name' => $item->name,
                'slug' => $item->slug,
                'address' => "{$item->city}, {$item->street} {$item->house}",
                'image' => $item->logo ? \Storage::url($item->logo) : asset('storage/clinics/logo/default-clinic.webp')
            ];
        });

    // 2. Врачи (Doctor)
    $doctors = \App\Models\Doctor::with('clinic')
        ->where(function($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('specialization', 'LIKE', "%{$query}%");
        })
        ->limit(5)->get()->map(function($item) {
            $clinicAddress = $item->clinic 
                ? " ({$item->clinic->city}, {$item->clinic->street} {$item->clinic->house})" 
                : "";

            return [
                'type' => 'doctor',
                'name' => $item->name,
                'slug' => $item->slug,
                'specialization' => $item->specialization,
                'clinic_info' => ($item->clinic->name ?? 'Частная практика') . $clinicAddress,
                'image' => $item->photo ? \Storage::url($item->photo) : asset('storage/doctors/default-doctor.webp')
            ];
        });

    // 3. Организации
    $organizations = \App\Models\Organization::with(['fieldOfActivity'])
        ->where('name', 'LIKE', "%{$query}%")
        ->limit(5)
        ->get()
        ->map(function($item) {
            return [
                'type' => 'organization',
                'name' => $item->name,
                'slug' => $item->slug,
                'category_name' => $item->fieldOfActivity->name ?? '', 
                'address' => "{$item->city}, {$item->street} {$item->house}",
                'image' => $item->logo ? \Storage::url($item->logo) : asset('storage/organizations/default-org.webp')
            ];
        });

    // 4. Специалисты (Specialist)
    $specialists = \App\Models\Specialist::with('organization')
        ->where(function($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
              ->orWhere('specialization', 'LIKE', "%{$query}%");
        })
        ->limit(5)->get()->map(function($item) {
            if ($item->organization) {
                $location = "{$item->organization->name} ({$item->organization->city}, {$item->organization->street} {$item->organization->house})";
            } else {
                $cityName = $item->city->name ?? 'Город не указан'; 
                $location = "Частный специалист: {$cityName}, {$item->street} {$item->house}";
            }

            return [
                'type' => 'specialist',
                'name' => $item->name,
                'slug' => $item->slug,
                'specialization' => $item->specialization,
                'location_info' => $location,
                'image' => $item->photo ? \Storage::url($item->photo) : asset('storage/doctors/default-doctor.webp')
            ];
        });

// 5. Животные (Поиск по породе ИЛИ по виду животного)
    $animals = \App\Models\Animal::with('details')
->where(function($q) use ($query) {
            $q->where(\DB::raw("CONCAT(species, ' ', breed)"), 'LIKE', "%{$query}%")
              ->orWhere('breed', 'LIKE', "%{$query}%")
              ->orWhere('species', 'LIKE', "%{$query}%");
        })
        ->limit(5)
        ->get()
        ->map(function($item) {
            $photoPath = $item->details->photo ?? null;

            return [
                'type' => 'Животное',
                'name' => $item->breed,
                'slug' => $item->breed_slug,
                'species_slug' => $item->species_slug, 
                'category' => $item->species,
                'image' => $photoPath ? \Storage::url($photoPath) : asset('storage/animals/default-animal.webp')
            ];
        });

    return response()->json([
        'clinics' => $clinics, 
        'doctors' => $doctors,
        'organizations' => $organizations,
        'specialists' => $specialists,
        'animals' => $animals
    ]);
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