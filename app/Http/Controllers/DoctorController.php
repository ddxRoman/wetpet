<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\City;

class DoctorController extends Controller
{
    // 🔹 Список докторов (страница /doctors)

public function index()
{
    // Определяем city_id из сессии или профиля пользователя
    $cityId = session('city_id');

    if (!$cityId && auth()->check()) {
        $cityId = auth()->user()->city_id; 
    }

    // Фильтруем доктора по city_id
    $doctorsQuery = Doctor::query();

    if (!empty($cityId)) {
        $doctorsQuery->where('city_id', $cityId);
        $selectedCity = City::find($cityId)?->name; // Для отображения названия города на странице
    } else {
        $selectedCity = null;
    }

    $doctors = $doctorsQuery->orderBy('name')->get();

    return view('pages.doctors.index', compact('doctors', 'selectedCity'));
}



    // 🔹 Передача докторов на welcome
    public function welcome()
    {
        $doctors = Doctor::orderBy('name')->limit(12)->get(); // Можно ограничить до, например, 12
        return view('welcome', compact('doctors'));
    }

public function show($id)
{
    // Подгружаем доктора и его клинику
    $doctor = Doctor::with('clinic')->findOrFail($id);

    // Проверяем, есть ли у доктора клиника (связь)
    $clinic = $doctor->clinic; // ❗ Это уже объект Clinic или null

    $reviews = $doctor->reviews()->with('user', 'photos')->latest()->get();

    

    return view('pages.doctors.show', compact('doctor', 'clinic'));
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

     public function destroy($id)
    {
        $clinic = Clinic::findOrFail($id);
        $clinic->delete();

        return redirect()->route('doctors.index')->with('success', 'Клиника удалена');
    }

    
}
