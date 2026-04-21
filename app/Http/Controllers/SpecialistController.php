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

class SpecialistController extends Controller
{
public function index(Request $request) 
{
    // Проверяем все возможные ключи сессии
    $cityId = session('selected_city_id') ?? session('city_id');

    // Если в сессии пусто, но ID пришел в ссылке (через фильтр) — берем его
    if (!$cityId && $request->has('city_id')) {
        $cityId = $request->city_id;
    }

    if (!$cityId) {
        return view('pages.specialists.index', [
            'doctors' => collect(),
            'specializations' => collect(),
            'selectedCity' => null,
            'selectedSpecialization' => null,
            'currentCityId' => null // Обязательно передаем, чтобы не было 500
        ]);
    }

    $city = \App\Models\City::find($cityId);
    
    $query = Specialist::with(['contacts', 'city', 'organization'])
        ->where('city_id', $cityId);

    if ($request->filled('specialization')) {
        $query->where('specialization', $request->specialization);
    }

    $specialists = $query->paginate(16);
    
    $allSpecializations = Specialist::where('city_id', $cityId)
        ->distinct()
        ->pluck('specialization');

    return view('pages.specialists.index', [
        'doctors' => $specialists,
        'specializations' => $allSpecializations,
        'selectedSpecialization' => $request->specialization,
        'selectedCity' => $city ? $city->name : null,
        'currentCityId' => $cityId, // ПЕРЕДАЕМ ЭТУ ПЕРЕМЕННУЮ
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
    // 1. Валидация (добавили поля контактов)
    $request->validate([
        'name' => 'required|string|max:255',
        'city_id' => 'required|exists:cities,id',
        'field_of_activity_id' => 'required|exists:field_of_activities,id',
        'phone' => 'nullable|string',
        'mail' => 'nullable|email', // в модалке поле называется "mail"
        'photo' => 'nullable|image|max:2048',
    ]);

    $field = FieldOfActivity::findOrFail($request->field_of_activity_id);

    // 2. Логика чекбоксов и организации
    $exotic = $request->has('exotic_animals') ? 'Да' : 'Нет';
    $onSite = $request->has('On_site_assistance') ? 'Да' : 'Нет';
    $orgId = $request->filled('clinic_id') ? $request->clinic_id : null;

    // 3. Обработка фото (если загружено)
    $photoPath = null;
    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('specialists', 'public');
    }

    // 4. Создание специалиста
    $specialist = Specialist::create([
        'name' => $request->name,
        'specialization' => $field->name,
        'city_id' => $request->city_id,
        'organization_id' => $orgId,
        'street' => $orgId ? null : $request->street,
        'house' => $orgId ? null : $request->house,
        'date_of_birth' => $request->date_of_birth,
        'experience' => $request->experience,
        'exotic_animals' => $exotic,
        'On_site_assistance' => $onSite,
        'description' => $request->description,
        'photo' => $photoPath, // Не забудьте сохранить путь к фото
        'slug' => Str::slug($request->name) . '-' . rand(100, 999),
    ]);

    // 5. СОХРАНЕНИЕ КОНТАКТОВ
    // Очистка WhatsApp если нужно (как в методе update)
    $whatsapp = null;
    if ($request->has('messengers') && in_array('whatsapp', $request->messengers)) {
        $whatsapp = preg_replace('/[^0-9]/', '', $request->phone);
    }

    $specialist->contacts()->create([
        'phone'    => $request->phone,
        'email'    => $request->mail, // в форме input name="mail"
        'telegram' => ($request->has('messengers') && in_array('telegram', $request->messengers)) ? $request->phone : null,
        'whatsapp' => $whatsapp,
    ]);

    return response()->json([
        'success' => true,
        'id' => $specialist->id,
    ]);
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
    // Ищем специалиста по slug с подгрузкой всех связей
    $specialist = Specialist::with(['contacts', 'city', 'organization'])
        ->where('slug', $slug)
        ->firstOrFail();

    // Передаем в шаблон как $doctor для совместимости с твоим кодом
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
