<?php

namespace App\Http\Controllers;

use App\Models\Specialist;
use App\Models\FieldOfActivity;
use App\Models\City;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SpecialistController extends Controller
{
    public function index() {

    

    }
    public function create() {}

    /**
     * ===============================
     * СОЗДАНИЕ (НЕ ТРОГАЕМ)
     * ===============================
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|string',
            'organization_id' => 'nullable|string',
            'description' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'experience' => 'nullable|integer',
            'exotic_animals' => 'nullable|string',
            'On_site_assistance' => 'nullable|string',
        ]);

        $field = FieldOfActivity::findOrFail($request->field_of_activity_id);

        $specialist = Specialist::create([
            'name' => $request->name,
            'specialization' => $field->name,
            'city_id' => $request->city_id,
            'organization_id' => $request->organization_id,
            'date_of_birth' => $request->date_of_birth,
            'experience' => $request->experience,
            'exotic_animals' => $request->exotic_animals,
            'On_site_assistance' => $request->On_site_assistance,
            'description' => $request->description,
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
    // ВСТАВЬ ЭТО:
    dd([
        'message' => 'Да, я работаю из SpecialistController',
        'specialist_city_id' => $specialist->city_id,
        'found_region' => $currentRegion
    ]);
    // Группировка полей
    $groupedFields = \App\Models\FieldOfActivity::where('type', 'specialist')
        ->orderBy('activity')
        ->orderBy('name')
        ->get()
        ->groupBy('activity');

    // 1. Получаем город. Если его нет, создаем пустой объект, чтобы не было ошибок
    $currentCity = $specialist->city_id ? City::find($specialist->city_id) : null;
    
    // 2. Явно объявляем переменную региона. 
    // Если города нет, регион будет null, но ПЕРЕМЕННАЯ БУДЕТ СУЩЕСТВОВАТЬ.
    $currentRegion = $currentCity ? $currentCity->region : null;

    // 3. Регионы для первого селекта
    $regions = City::select('region')
        ->whereNotNull('region')
        ->distinct()
        ->orderBy('region')
        ->pluck('region');

    // 4. Города для текущего региона
    $cities = $currentRegion 
        ? City::where('region', $currentRegion)->orderBy('name')->get() 
        : collect();

    // 5. Организации
    $organizations = $specialist->city_id
        ? Organization::where('city_id', $specialist->city_id)->get()
        : collect();

    // Передаем всё в compact. Проверь, чтобы имя в compact совпадало с именем переменной!
    return view('account.tabs.specialist-profile', compact(
        'specialist',
        'groupedFields',
        'regions',
        'cities',
        'organizations',
        'currentCity',
        'currentRegion' 
    ));
}

    /**
     * ===============================
     * ОБНОВЛЕНИЕ
     * ===============================
     */



public function update(Request $request, Specialist $specialist)
{
    // 1. Считаем лимит стажа (на основе даты рождения из запроса)
    $maxExp = 0;
    if ($request->date_of_birth) {
        $yearsOld = \Carbon\Carbon::parse($request->date_of_birth)->age;
        $maxExp = max(0, $yearsOld - 18);
    }

    // 2. ЕДИНАЯ ВАЛИДАЦИЯ (Пишем сюда ВСЕ поля, которые есть в форме)
    $validated = $request->validate([
        'name'               => 'required|string|max:255',
        'specialization'     => 'nullable|string',
        'date_of_birth'      => 'nullable|date',
        'experience'         => "nullable|integer|min:0|max:$maxExp",
        'city_id'            => 'nullable|exists:cities,id',
        'organization_id'    => 'nullable|exists:organizations,id',
        'description'        => 'nullable|string',
        'exotic_animals'     => 'nullable|string',
        'On_site_assistance' => 'nullable|string',
        'phone'              => 'nullable|string',
        'email'              => 'nullable|email',
        'photo'              => 'nullable|image|max:2048',
        // Добавь сюда любые другие поля, которые видишь в request_all, но нет в validated
    ], [
        'experience.max' => "Стаж не может превышать $maxExp лет.",
    ]);

    // 3. Обработка фото (если оно пришло)
    if ($request->hasFile('photo')) {
        if ($specialist->photo) {
            Storage::disk('public')->delete($specialist->photo);
        }
        $validated['photo'] = $request->file('photo')->store('specialists', 'public');
    }

    // 4. Добавляем Slug и другие тех. поля в массив для обновления
    $validated['slug'] = \Illuminate\Support\Str::slug($request->name);

    // --- ПРОВЕРКА ПЕРЕД СОХРАНЕНИЕМ ---
    // Если после этого исправления в validated_data всё еще меньше полей, 
    // значит их названия в форме не совпадают с названиями тут.
    // dd($validated); 

    // 5. Обновление специалиста
    $specialist->update($validated);

    // 6. Обновление контактов
    $specialist->contacts()->updateOrCreate(
        ['specialist_id' => $specialist->id],
        [
            'phone'    => $request->phone,
            'email'    => $request->email,
            'telegram' => $request->has('telegram'),
            'whatsapp' => $request->has('whatsapp'),
            'max'      => $request->has('max'),
        ]
    );

    return redirect()->back()->with('success', 'Данные успешно обновлены');
}


public function destroy(Specialist $specialist)
{
    // 1. Удаляем фото, если оно есть
    if ($specialist->photo) {
        Storage::disk('public')->delete($specialist->photo);
    }

    // 2. Удаляем связанные контакты (если не настроено каскадное удаление в БД)
    $specialist->contacts()->delete();

    // 3. Удаляем самого специалиста
    $specialist->delete();

    return redirect()->route('account')->with('success', 'Специалист успешно удален');
}


}