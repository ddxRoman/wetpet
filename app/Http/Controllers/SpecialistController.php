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
use Illuminate\Support\Str;

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

    // ВАЖНО: Проверь, чтобы эти имена были в compact
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
            'description'        => 'nullable|string',
            'exotic_animals'     => 'nullable|string',
            'On_site_assistance' => 'nullable|string',
            'phone'              => 'nullable|string',
            'email'              => 'nullable|email',
            'photo'              => 'nullable|image|max:2048',
        ], [
            'experience.max' => "Стаж не может превышать $maxExp лет.",
        ]);

        if ($request->hasFile('photo')) {
            if ($specialist->photo) {
                Storage::disk('public')->delete($specialist->photo);
            }
            $validated['photo'] = $request->file('photo')->store('specialists', 'public');
        }

        $validated['slug'] = Str::slug($request->name);
        $specialist->update($validated);

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

        return redirect()->to(route('account') . '#specialist-profile')
        ->with('success', 'Данные успешно обновлены');
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
