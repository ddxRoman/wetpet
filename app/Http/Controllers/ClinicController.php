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

    // 1) Если пользователь авторизован — берём город из users.city_id
    if ($user && $user->city_id) {
        $city = City::find($user->city_id);
        $selectedCity = $city?->name;
    }
    // 2) Если пользователь не авторизован — берём из сессии
    else {
        $selectedCity = session('selected_city');
    }

    // 3) Фильтрация клиник по городу
    $clinics = Clinic::when($selectedCity, function ($query, $city) {
        return $query->whereRaw('LOWER(TRIM(city)) = LOWER(TRIM(?))', [$city]);
    })->get();

    // 4) Если selectedCity появился — обновляем сессию для неавторизованных пользователей
    if (!$user && $selectedCity) {
        session(['selected_city' => $selectedCity]);
    }

    return view('pages.clinics.index', compact('clinics', 'selectedCity'));
}



    // Просмотр одной клиники
    public function show($id)
    {
        $clinic = Clinic::findOrFail($id);
        return view('pages.clinics.show', compact('clinic'));
        
        // Загружаем клинику вместе с наградами
    $clinic = Clinic::with('awards')->findOrFail($id);

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
    \Log::info("=== clinicsByCity START ===");
    \Log::info("Received cityId: " . $cityId);

    $city = City::find($cityId);

    if (!$city) {
        \Log::info("City NOT FOUND for ID: " . $cityId);
        return response()->json([]);
    }

    \Log::info("Found city: " . json_encode($city->toArray()));

    // Какие строки реально сравниваются
    \Log::info("Comparing clinics.city with city->name: '" . $city->name . "'");

    $clinics = Clinic::whereRaw(
        'LOWER(TRIM(city)) = LOWER(TRIM(?))',
        [$city->name]
    )->get();

    \Log::info("Clinics query result: " . $clinics->count());
    \Log::info("Clinics list: " . $clinics->toJson());

    \Log::info("=== clinicsByCity END ===");

    return response()->json($clinics);
}





    // Форма редактирования
    public function edit($id)
    {
        $clinic = Clinic::findOrFail($id);
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

        return redirect()->route('pages.clinics.show', $clinic->id)
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
