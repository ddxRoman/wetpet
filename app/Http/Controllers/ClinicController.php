<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    // Список всех клиник
public function index(Request $request)
{
    // Получаем выбранный город из сессии или запроса
    $selectedCity = $request->input('city', session('selected_city'));

    // Если город выбран — фильтруем по нему, иначе показываем все
    $clinics = Clinic::when($selectedCity, function ($query, $city) {
        return $query->where('city', $city);
    })->get();

    // Сохраняем выбранный город в сессию (чтобы не сбрасывался при переходах)
    if ($selectedCity) {
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
            'logo' => 'nullable|string|max:255',
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
            'logo' => 'nullable|string|max:255',
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
