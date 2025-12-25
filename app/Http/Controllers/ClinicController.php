<?php

namespace App\Http\Controllers;
    use App\Models\City;
use App\Models\Clinic;

use Illuminate\Http\Request;

class ClinicController extends Controller
{
    // Список всех клиник
public function index(Request $request)
{
    $user = auth()->user();

    if ($user && $user->city_id) {
        $city = City::find($user->city_id);
        $selectedCity = $city?->name;
    } else {
        $selectedCity = session('city_name');
    }

    $clinics = Clinic::when($selectedCity, function ($query, $city) {
        $query->whereRaw(
            'LOWER(TRIM(city)) = LOWER(TRIM(?))',
            [$city]
        );
    })->get();

    // 🔴 ВАЖНО: если AJAX — возвращаем ТОЛЬКО список
    if ($request->ajax()) {
        return view('pages.clinics.partials.list', compact('clinics'))->render();
    }

    return view('pages.clinics.index', compact('clinics', 'selectedCity'));
}

    // Просмотр одной клиники
public function show(Clinic $clinic)
{
    $clinic->load('awards');

    return view('pages.clinics.show', compact('clinic'));
}


    // Форма добавления новой клиники
    public function create()
    {
        return view('pages.clinics.create');
    }

    // Сохранение новой клиники
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'region' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'street' => 'required|string|max:255',
            'house' => 'nullable|string|max:50',
            'address_comment' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'nullable|string',
            'phone1' => 'nullable|string|max:30',
            'phone2' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'telegram' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'schedule' => 'nullable|string|max:100',
            'workdays' => 'nullable|string|max:100',
        ]);

        Clinic::create($data);

        return redirect()->route('clinics.index')->with('success', 'Клиника добавлена');
    }

public function clinicsByCity($cityId)
{

    $city = City::find($cityId);

    if (!$city) {
        return response()->json([]);
    }

    $clinics = Clinic::whereRaw(
        'LOWER(TRIM(city)) = LOWER(TRIM(?))',
        [$city->name]
    )->get();
    return response()->json($clinics);
}





    // Форма редактирования
public function edit(Clinic $clinic)
{
    return view('pages.clinics.edit', compact('clinic'));
}


    // Обновление клиники
    public function update(Request $request, $id)
    {
        $clinic = Clinic::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'region' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'street' => 'required|string|max:255',
            'house' => 'nullable|string|max:50',
            'address_comment' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'nullable|string',
            'phone1' => 'nullable|string|max:30',
            'phone2' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'telegram' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'schedule' => 'nullable|string|max:100',
            'workdays' => 'nullable|string|max:100',
        ]);

        $clinic->update($data);

        return redirect()->route('pages.clinics.show', $clinic->slug)
                         ->with('success', 'Клиника обновлена');
    }

    // Удаление
    public function destroy($id)
    {
        $clinic = Clinic::findOrFail($id);
        $clinic->delete();

        return redirect()->route('clinics.index')->with('success', 'Клиника удалена');
    }
}
