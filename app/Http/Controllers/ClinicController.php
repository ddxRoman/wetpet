<?php

namespace App\Http\Controllers;
    use App\Models\City;
use App\Models\Clinic;
use App\Services\TelegramService;


use Illuminate\Http\Request;

class ClinicController extends Controller
{
    // Список всех клиник
public function index(Request $request)
{
    $country = 'Россия';
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
$clinic->load('doctors'); // city уже строка

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
        'region' => 'nullable|string|max:100',
        'city_id' => 'required|exists:cities,id',
        'street' => 'required|string|max:255',
        'house' => 'nullable|string|max:50',
        'address_comment' => 'nullable|string|max:255',
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
        'description' => 'nullable|string',
        'phone1' => 'nullable|string|max:30',
        'phone2' => 'nullable|string|max:30',
        'email' => 'nullable|email|max:255',
        'telegram' => 'nullable|string|max:255',
        'whatsapp' => 'nullable|string|max:255',
        'schedule' => 'nullable|string|max:100',
        'workdays' => 'nullable|string|max:100',
    ]);

    $city = City::findOrFail($data['city_id']);

    $clinic = Clinic::create([
        'name' => $data['name'],
        'country' => 'Россия',
        'region' => $data['region'] ?? null,
        'city' => $city->name,
        'street' => $data['street'],
        'house' => $data['house'] ?? null,
        'address_comment' => $data['address_comment'] ?? null,
        'description' => $data['description'] ?? null,
        'phone1' => $data['phone1'] ?? null,
        'phone2' => $data['phone2'] ?? null,
        'email' => $data['email'] ?? null,
        'schedule' => $data['schedule'] ?? null,
        'workdays' => $data['workdays'] ?? null,
    ]);

    // 🔔 TELEGRAM — ДО return
    $user = auth()->user();

    app(TelegramService::class)->send(
        "🏥 <b>Новая клиника</b>\n\n" .
        "Название: {$clinic->name}\n" .
        "Город: {$clinic->city}\n" .
        "Адрес: {$clinic->street} {$clinic->house}\n\n" .
        "👤 <b>Добавил:</b>\n" .
        "Имя: " . ($user?->name ?? 'Гость') . "\n" .
        "Email: " . ($user?->email ?? '—') . "\n\n" .
        "🏷 <b>Пользователь добавил свою организацию</b>"
    );

    return redirect()
        ->route('clinics.show', $clinic)
        ->with('success', 'Клиника добавлена');
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
            'region' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'street' => 'required|string|max:255',
            'house' => 'nullable|string|max:50',
            'address_comment' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:webp|max:4096',
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
