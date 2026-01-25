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
    public function index() {}
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
    // Получаем все специализации типа specialist
    $allFields = \App\Models\FieldOfActivity::where('type', 'specialist')
        ->orderBy('activity')
        ->orderBy('name')
        ->get();

    // Группируем их по полю activity (doctor и прочие)
    $groupedFields = $allFields->groupBy('activity');

    $currentCity = $specialist->city_id ? City::find($specialist->city_id) : null;

    $regions = City::select('region')
        ->whereNotNull('region')
        ->distinct()
        ->orderBy('region')
        ->pluck('region');

    // Для организаций важно искать по city_id (внешний ключ), а не по имени
    $organizations = $specialist->city_id
        ? Organization::where('city_id', $specialist->city_id)->get()
        : collect();

    return view('account.tabs.specialist-profile', compact(
        'specialist',
        'groupedFields', // Передаем сгруппированные данные
        'regions',
        'organizations',
        'currentCity'
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
            'specialization' => 'required|string',
            'date_of_birth' => "nullable|date|after_or_equal:1950-01-01|before_or_equal:$maxBirthDate",
            'city_id' => 'required|exists:cities,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'experience' => 'nullable|integer|min:0',
            'exotic_animals' => 'required|in:Да,Нет',
            'On_site_assistance' => 'required|in:Да,Нет',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('photo')) {
            if ($specialist->photo) {
                Storage::delete('public/' . $specialist->photo);
            }
            $specialist->photo = $request->file('photo')->store('specialists', 'public');
        }

        $specialist->update($request->except('photo'));

        return redirect()->back()->with('success', 'Профиль специалиста обновлён');
    }
}
