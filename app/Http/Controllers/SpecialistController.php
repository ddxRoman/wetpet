<?php

namespace App\Http\Controllers;

use App\Models\Specialist;
use App\Models\FieldOfActivity;
use App\Models\City;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Doctor;

class SpecialistController extends Controller
{
public function index(Request $request)
{
    $user = auth()->user();
    $cityId = null;
    $selectedCity = null;

    // 1. Определение города
    if ($request->filled('city_id')) {
        $cityId = (int) $request->get('city_id');
        if (!$user) { session(['city_id' => $cityId]); }
    } elseif ($user && $user->city_id) {
        $cityId = $user->city_id;
    } else {
        $cityId = session('city_id');
    }

    if ($cityId) { 
        $selectedCity = City::find($cityId)?->name; 
    }

    $selectedSpecialization = $request->get('specialization');

    // 2. ТЕГИ: Берем только те, что относятся к специалистам, а не к врачам
    $specializations = FieldOfActivity::query()
        ->where('type', 'specialist') 
        ->where('activity', '!=', 'doctor') // Исключаем врачебные специальности
        ->orderBy('name')
        ->distinct()
        ->pluck('name'); 

    // 3. ЗАПРОС: Фильтруем список специалистов
    $items = Specialist::withAvg('reviews', 'rating')
        ->when($cityId, function ($q) use ($cityId) {
            $q->where('city_id', $cityId);
        })
        ->when($selectedSpecialization, function ($q) use ($selectedSpecialization) {
            $searchTerm = mb_substr($selectedSpecialization, 0, -3);
            if (mb_strlen($searchTerm) < 3) { $searchTerm = $selectedSpecialization; }
            $q->where('specialization', 'LIKE', '%' . $searchTerm . '%');
        })
        ->orderByDesc('reviews_avg_rating') 
        ->orderBy('name')
        ->paginate(16)
        ->withQueryString();

    return view('pages.specialists.index', [
        'specialists' => $items, 
        'selectedCity' => $selectedCity,
        'specializations' => $specializations, // Теперь тут только нужные теги
        'selectedSpecialization' => $selectedSpecialization,
        'currentCityId' => $cityId
    ]);
}

    public function create() {}

    /**
     * ===============================
     * СОЗДАНИЕ (НЕ ТРОГАЕМ)
     * ===============================
     */
public function store(Request $request)
{
    // 1. Валидация
    $validated = $request->validate([
        'name'                 => 'required|string|max:255',
        'field_of_activity_id' => 'required|exists:field_of_activities,id',
        'city_id'              => 'nullable|exists:cities,id',
        'clinic_id'            => 'nullable|exists:clinics,id',
        'experience'           => 'nullable|string|max:255',
        'description'          => 'nullable|string',
        'exotic_animals'       => 'nullable|string', 
        'On_site_assistance'   => 'nullable|string',
        'phone'                => 'nullable|string|max:255',
        'mail'                 => 'nullable|string|email|max:255',
        'messengers'           => 'nullable|array',
        'photo'                => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120', // Валидация фото
    ]);

    // 🔹 Получаем специализацию
    $field = FieldOfActivity::findOrFail($validated['field_of_activity_id']);

    // 🔹 Обработка фото (СОХРАНЕНИЕ НА ДИСК)
    $photoPath = null;
    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('doctors', 'public');
    }

    // 🔹 Создаём врача
    $doctor = Doctor::create([
        'name'                 => $validated['name'],
        'specialization'       => $field->name,
        'field_of_activity_id' => $field->id,
        'city_id'              => $validated['city_id'] ?? null,
        'clinic_id'            => $validated['clinic_id'] ?? null,
        'experience'           => $validated['experience'] ?? null,
        'description'          => $validated['description'] ?? null,
        'exotic_animals'       => $request->has('exotic_animals') ? 'Да' : 'Нет',
        'On_site_assistance'   => $request->has('On_site_assistance') ? 'Да' : 'Нет',
        'photo'                => $photoPath, // ЗАПИСЬ В БАЗУ
        'slug'                 => Str::slug($validated['name']) . '-' . rand(100, 999),
    ]);

    // СОХРАНЕНИЕ КОНТАКТОВ
    $telegram = ($request->messengers && in_array('telegram', $request->messengers)) ? $request->phone : null;
    $whatsapp = ($request->messengers && in_array('whatsapp', $request->messengers)) ? $request->phone : null;
    $max      = ($request->messengers && in_array('messenger', $request->messengers)) ? $request->phone : null;

    $doctor->contacts()->create([
        'phone'    => $request->phone,
        'email'    => $request->mail,
        'telegram' => $telegram,
        'whatsapp' => $whatsapp,
        'max'      => $max,
    ]);

    // Уведомление в Telegram
    try {
        $url = route('doctors.show', $doctor->slug); 
        Http::post('https://api.telegram.org/bot' . config('services.telegram.bot_token') . '/sendMessage', [
            'chat_id' => config('services.telegram.chat_id'),
            'parse_mode' => 'HTML',
            'text' => "🩺 <b>Новый специалист</b>\n\n" . "👤 <b>Имя:</b> {$doctor->name}\n" . "📌 <b>Специализация:</b> {$doctor->specialization}\n" . "\n🔗 <a href=\"{$url}\">Открыть профиль</a>",
        ]);
    } catch (\Throwable $e) {
        logger()->warning('Telegram notify failed', ['error' => $e->getMessage()]);
    }

    if ($request->boolean('its_me') && auth()->check()) {
        $doctor->owners()->syncWithoutDetaching([auth()->id() => ['is_confirmed' => false]]);
    }

    return response()->json(['success' => true, 'id' => $doctor->id, 'type' => 'doctor']);
}

    /**
     * ===============================
     * РЕДАКТИРОВАНИЕ
     * ===============================
     */
public function edit(Specialist $specialist)
{
    // 1. Врачи (activity == doctor)
    $doctorFields = \App\Models\FieldOfActivity::where('type', 'specialist')
        ->where('activity', 'doctor')
        ->orderBy('name')
        ->get();

    // 2. НЕ врачи (все остальные)
    $otherSpecialistFields = \App\Models\FieldOfActivity::where('type', 'specialist')
        ->where('activity', '!=', 'doctor')
        ->orderBy('name')
        ->get();

    // 3. Логика локации (как и была)
    $currentCity = $specialist->city_id ? City::find($specialist->city_id) : null;
    $currentRegion = $currentCity ? $currentCity->region : null;

    $regions = City::select('region')
        ->whereNotNull('region')
        ->distinct()
        ->orderBy('region')
        ->pluck('region');

    // Все города (обязательно для селекта в Blade)
    $allCities = City::select('id', 'name', 'region')->orderBy('name')->get();

    $organizations = $specialist->city_id
        ? Organization::where('city_id', $specialist->city_id)->get()
        : collect();

    return view('account.tabs.specialist-profile', compact(
        'specialist',
        'doctorFields',
        'otherSpecialistFields',
        'regions',
        'allCities',
        'organizations',
        'currentCity',
        'currentRegion' 
    ));
}

public function update(Request $request, Specialist $specialist)
{
    $maxExp = 0;
    if ($request->date_of_birth) {
        $yearsOld = Carbon::parse($request->date_of_birth)->age;
        $maxExp = max(0, $yearsOld - 18);
    }

    $validated = $request->validate([
        'name'               => 'required|string|max:255',
        'specialization'     => 'nullable|string',
        'date_of_birth'      => 'nullable|date',
        'experience'         => "nullable|integer|min:0|max:$maxExp",
        'city_id'            => 'nullable|exists:cities,id',
        'organization_id'    => 'nullable|exists:organizations,id',
        'street'             => 'nullable|string|max:255', 
        'house'              => 'nullable|string|max:20',
        'description'        => 'nullable|string',
        'phone'              => 'nullable|string',
        'email'              => 'nullable|email',
        'photo'              => 'nullable|image|max:2048',
        'telegram'           => 'nullable|string|max:255',
        'whatsapp'           => 'nullable|string|max:255', 
        'max'                => 'nullable|string|max:255',
    ], [
        'experience.max' => "Стаж не может превышать $maxExp лет.",
    ]);

    // Обработка чекбоксов Да/Нет
    $validated['exotic_animals'] = $request->has('exotic_animals') ? 'Да' : 'Нет';
    $validated['On_site_assistance'] = $request->has('On_site_assistance') ? 'Да' : 'Нет';

    // Если организация не выбрана (частник), зануляем связь
    if (!$request->filled('organization_id')) {
        $validated['organization_id'] = null;
    } else {
        // Если выбрана организация, зануляем ручной адрес
        $validated['street'] = null;
        $validated['house'] = null;
    }

    // Обработка фото
    if ($request->hasFile('photo')) {
        if ($specialist->photo) {
            Storage::disk('public')->delete($specialist->photo);
        }
        $validated['photo'] = $request->file('photo')->store('specialists', 'public');
    }

    // Чистим WhatsApp
    $whatsappClean = $request->whatsapp ? preg_replace('/[^0-9]/', '', $request->whatsapp) : null;
    
    // Генерируем Slug
    $validated['slug'] = Str::slug($request->name);

    // 1. Обновляем модель специалиста
    $specialist->update($validated);

    // 2. Обновляем связанные контакты
    $specialist->contacts()->updateOrCreate(
        ['specialist_id' => $specialist->id],
        [
            'phone'    => $request->phone,
            'email'    => $request->email,
            'telegram' => $request->telegram,
            'whatsapp' => $whatsappClean,
            'max'      => $request->max,
        ]
    );

    return redirect()->to(route('account') . '#specialist-profile')
        ->with('success', 'Данные успешно обновлены');
}

public function show($slug)
{
    // Ищем специалиста по slug с подгрузкой связей и АГРЕГАТНЫМИ данными
    $specialist = Specialist::with(['contacts', 'city', 'organization'])
        ->withCount('reviews') // Это создаст переменную $doctor->reviews_count
        ->withAvg('reviews', 'rating') // Это создаст переменную $doctor->reviews_avg_rating
        ->where('slug', $slug)
        ->firstOrFail();

    // Передаем в шаблон как $doctor
    return view('pages.specialists.show', ['doctor' => $specialist]);
}

    public function destroy(Specialist $specialist)
    {
        if ($specialist->photo) {
            Storage::disk('public')->delete($specialist->photo);
        }
        $specialist->contacts()->delete();
        $specialist->delete();

 return redirect()->to(route('account') . '#specialist-profile')
        ->with('success', 'Данные успешно обновлены'); }
}
