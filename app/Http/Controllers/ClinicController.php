<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Clinic;
use Illuminate\Support\Facades\Storage;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    /**
     * Список всех клиник с сортировкой по рейтингу
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user && $user->city_id) {
            $city = City::find($user->city_id);
            $selectedCity = $city?->name;
        } else {
            $selectedCity = session('city_name');
        }

        $clinics = Clinic::withAvg('reviews', 'rating')
            ->when($selectedCity, function ($query, $city) {
                $query->whereRaw(
                    'LOWER(TRIM(city)) = LOWER(TRIM(?))',
                    [$city]
                );
            })
            ->orderByDesc('reviews_avg_rating')
            ->get();

        if ($request->ajax()) {
            return view('pages.clinics.partials.list', compact('clinics'))->render();
        }

        return view('pages.clinics.index', compact('clinics', 'selectedCity'));
    }

    /**
     * Просмотр одной клиники
     */
    public function show(Clinic $clinic)
    {
        $clinic->load(['awards', 'doctors']);
        return view('pages.clinics.show', compact('clinic'));
    }

    /**
     * Форма добавления новой клиники
     */
    public function create()
    {
        // Для селекта городов в шаблоне создания
        $allCities = City::orderBy('name')->get();
        return view('pages.clinics.create', compact('allCities'));
    }

    /**
     * Сохранение новой клиники
     */
public function store(Request $request)
{
    $validated = $request->validate([
        'name'            => 'required|string|max:255',
        'city_id'         => 'required|exists:cities,id', 
        'street'          => 'required|string|max:255',
        'house'           => 'nullable|string|max:50',
        'address_comment' => 'nullable|string|max:255',
        'logo'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
        'description'     => 'nullable|string',
        'phone1'          => 'nullable|string|max:30',
        'phone2'          => 'nullable|string|max:30',
        'email'           => 'nullable|email|max:255',
        'schedule'        => 'nullable|string|max:100',
        'workdays'        => 'nullable|string|max:100',
    ]);

    $cityModel = City::findOrFail($validated['city_id']);

    $path = $request->hasFile('logo') 
        ? $request->file('logo')->store('clinics', 'public') 
        : null;

    // Добавляем город и регион в массив перед созданием
    $data = array_merge($validated, [
        'country' => 'Россия',
        'city'    => $cityModel->name,
        'region'  => $cityModel->region,
        'logo'    => $path,
    ]);

    $clinic = Clinic::create($data);

    try {
        app(TelegramService::class)->send(
            "🏥 <b>Новая клиника</b>\n\n" .
            "Название: {$clinic->name}\n" .
            "Город: {$clinic->city}\n" .
            "Регион: {$clinic->region}\n" .
            "Адрес: {$clinic->street} {$clinic->house}"
        );
    } catch (\Exception $e) {
        \Log::error("TG Error: " . $e->getMessage());
    }

    return redirect()->route('clinics.show', $clinic)->with('success', 'Клиника добавлена');
}

    /**
     * Форма редактирования
     */
    public function edit(Clinic $clinic)
    {
        $allCities = City::orderBy('name')->get();
        return view('pages.clinics.edit', compact('clinic', 'allCities'));
    }

    /**
     * Обновление клиники
     */
public function update(Request $request, $id)
{
    $clinic = Clinic::findOrFail($id);

    $validated = $request->validate([
        'name'            => 'required|string|max:255',
        'city_id'         => 'nullable|exists:cities,id', 
        'street'          => 'required|string|max:255',
        'house'           => 'nullable|string|max:50',
        'address_comment' => 'nullable|string|max:255',
        'logo'            => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:8192',
        'description'     => 'nullable|string',
        'phone1'          => 'nullable|string|max:30',
        'phone2'          => 'nullable|string|max:30',
        'email'           => 'nullable|email|max:255',
        'telegram'        => 'nullable|string|max:255',
        'whatsapp'        => 'nullable|string|max:255',
        'schedule'        => 'nullable|string|max:100',
        'workdays'        => 'nullable|string|max:100',
    ]);

    if ($request->filled('city_id')) {
        $cityModel = City::find($request->city_id);
        if ($cityModel) {
            $validated['city'] = $cityModel->name;
            $validated['region'] = $cityModel->region;
        }
    }

    if ($request->hasFile('logo')) {
        if ($clinic->logo) {
            Storage::disk('public')->delete($clinic->logo);
        }
        $validated['logo'] = $request->file('logo')->store('clinics', 'public');
    }

    $clinic->update($validated);

    return redirect()->to(url('/account'))
        ->withFragment('my-clinics')
        ->with('success', 'Данные клиники обновлены');
}

    /**
     * Удаление
     */
    public function destroy($id)
    {
        $clinic = Clinic::findOrFail($id);
        
        if ($clinic->logo) {
            Storage::disk('public')->delete($clinic->logo);
        }
        
        $clinic->delete();

        return redirect()->to(url('/account'))
            ->withFragment('my-clinics')
            ->with('success', 'Клиника удалена');
    }

    /**
     * Живой поиск и API методы (оставляем как были)
     */
    public function liveSearch(Request $request)
    {
        $query = $request->get('q');
        if (mb_strlen($query) < 2) return response()->json([]);

        $clinics = Clinic::where('name', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get(['name', 'city', 'street', 'house', 'slug', 'logo'])
            ->map(function($item) {
                return [
                    'type' => 'clinic',
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'address' => "{$item->city}, {$item->street} {$item->house}",
                    'image' => $item->logo ? Storage::url($item->logo) : asset('storage/clinics/logo/default.webp')
                ];
            });

        // Тут можно добавить поиск врачей, если нужно, как в твоем исходнике
        return response()->json(['clinics' => $clinics]);
    }

    public function clinicsByCity($cityId)
    {
        $city = City::find($cityId);
        if (!$city) return response()->json([]);

        $clinics = Clinic::withAvg('reviews', 'rating')
            ->whereRaw('LOWER(TRIM(city)) LIKE LOWER(TRIM(?))', ["%{$city->name}%"])
            ->orderByDesc('reviews_avg_rating')
            ->get();

        return response()->json($clinics);
    }
}