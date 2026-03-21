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
        $maxBirthDate = now()->subYears(18)->format('Y-m-d');

        $request->validate([
            'name' => 'required|string|max:255',
            'field_of_activity_id' => 'required|exists:field_of_activities,id', // Исправлено
            'date_of_birth' => "nullable|date|after_or_equal:1950-01-01|before_or_equal:$maxBirthDate",
            'city_id' => 'required|exists:cities,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'experience' => 'nullable|integer|min:0',
            'exotic_animals' => 'required|in:Да,Нет',
            'On_site_assistance' => 'required|in:Да,Нет',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'description' => 'nullable|string',
            'messengers' => 'nullable|array', // Добавлено
        ]);

    // ... твоя валидация ...

    // 1. Обновляем самого специалиста
    $specialist->update($request->only(['name', 'city_id', 'organization_id', 'description', 'experience', 'exotic_animals', 'On_site_assistance']));

    // 2. Обновляем или создаем контакты
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



        // Получаем название специализации по ID
        $field = FieldOfActivity::findOrFail($request->field_of_activity_id);

        $data = $request->except('photo', 'messengers');
        $data['specialization'] = $field->name; // Записываем имя специализации
        $data['field_of_activity_id'] = $request->field_of_activity_id;
        $data['messengers'] = json_encode($request->messengers); // Сохраняем как JSON

        if ($request->hasFile('photo')) {
            if ($specialist->photo) {
                Storage::delete('public/' . $specialist->photo);
            }
            $data['photo'] = $request->file('photo')->store('specialists', 'public');
        }

        $specialist->update($data);

        return redirect()->back()->with('success', 'Профиль специалиста обновлён');
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