<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\City;

class DoctorController extends Controller
{
    // 🔹 Список докторов (страница /doctors)

public function index(\Illuminate\Http\Request $request)
{
    $user = auth()->user();
    $cityId = null;
    $selectedCity = null;

    // 1️⃣ Если city_id пришёл из запроса (AJAX)
    if ($request->filled('city_id')) {
        $cityId = (int) $request->get('city_id');

        if (!$user) {
            session(['city_id' => $cityId]);
        }
    }
    // 2️⃣ Если пользователь авторизован
    elseif ($user && $user->city_id) {
        $cityId = $user->city_id;
    }
    // 3️⃣ Берём из сессии
    else {
        $cityId = session('city_id');
    }

    // Название города для отображения
    if ($cityId) {
        $selectedCity = City::find($cityId)?->name;
    }

    // Фильтрация докторов
    $doctors = Doctor::when($cityId, function ($query) use ($cityId) {
        $query->where('city_id', $cityId);
    })
    ->orderBy('name')
    ->get();

    return view('pages.doctors.index', compact('doctors', 'selectedCity'));
}


    // 🔹 Передача докторов на welcome
    public function welcome()
    {
        $doctors = Doctor::orderBy('name')->limit(120)->get(); // Можно ограничить до, например, 12
        return view('welcome', compact('doctors'));
    }

public function show(Doctor $doctor)
{
    $doctor->load([
        'city',
        'clinic',
        'contacts',
        'services' => function ($q) use ($doctor) {
            $q->where('specialization_doctor', $doctor->specialization);
        }
    ]);

    $clinic = $doctor->clinic;

    $reviews = $doctor->reviews()
        ->with('user', 'photos')
        ->latest()
        ->get();

    return view('pages.doctors.show', compact(
        'doctor',
        'clinic',
        'reviews'
    ));
}



     public function update(Request $request, Doctor $doctor)
    {
        // Проверка прав — если нужно: убедиться, что текущий пользователь может редактировать
        // if (auth()->id() !== $doctor->user_id) { abort(403); }

        $request->validate([
            'name' => 'required|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'clinic' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        $data = $request->only(['name','specialization','clinic','city','experience','description']);

        // Фото
        if ($request->hasFile('photo')) {
            // удаляем старое если есть
            if (!empty($doctor->photo) && Storage::disk('public')->exists($doctor->photo)) {
                Storage::disk('public')->delete($doctor->photo);
            }
            // сохраняем новое (в папку doctors)
            $path = $request->file('photo')->store('doctors', 'public');
            $data['photo'] = $path;
        }

        $doctor->update($data);

        // если форма обычная HTML — редирект обратно с флеш-сообщением
        return redirect()->back()->with('success', 'Данные врача сохранены');
        
        // если ожидался JSON (AJAX) — можно вернуть JSON:
        // return response()->json(['success' => true, 'doctor' => $doctor->fresh()]);
    }

public function destroy(Doctor $doctor)
{
    $doctor->delete();

    return redirect()
        ->route('doctors.index')
        ->with('success', 'Врач удалён');
}

    
}
