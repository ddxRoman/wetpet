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
        $doctor = Doctor::findOrFail($id);
        return view('pages.doctors.show', compact('doctor'));
    }
    
}
