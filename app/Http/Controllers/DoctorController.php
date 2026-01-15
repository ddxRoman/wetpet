<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\City;
use App\Models\FieldOfActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    /**
     * 🔹 Создание врача (AJAX, модалка, Telegram)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'field_of_activity_id' => 'required|exists:field_of_activities,id',
            'city_id' => 'nullable|exists:cities,id',
            'clinic_id' => 'nullable|exists:clinics,id',
            'experience' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        // 🔹 Получаем специализацию
        $field = FieldOfActivity::findOrFail($validated['field_of_activity_id']);

        // 🔹 Создаём врача
        $doctor = Doctor::create([
            'name' => $validated['name'],
            'specialization' => $field->name,
            'field_of_activity_id' => $field->id,
            'city_id' => $validated['city_id'] ?? null,
            'clinic_id' => $validated['clinic_id'] ?? null,
            'experience' => $validated['experience'] ?? null,
            'description' => $validated['description'] ?? null,
            'slug' => Str::slug($validated['name']),
        ]);

        // 🔹 Уведомление в Telegram
        try {
            Http::post('https://api.telegram.org/bot' . config('services.telegram.bot_token') . '/sendMessage', [
                'chat_id' => config('services.telegram.chat_id'),
                'parse_mode' => 'HTML',
                'text' =>
                    "🩺 <b>Новый специалист</b>\n\n" .
                    "👤 <b>Имя:</b> {$doctor->name}\n" .
                    "📌 <b>Специализация:</b> {$doctor->specialization}\n" .
                    ($doctor->city?->name ? "🏙 <b>Город:</b> {$doctor->city->name}\n" : '') .
                    ($doctor->clinic?->name ? "🏥 <b>Клиника:</b> {$doctor->clinic->name}\n" : ''),
            ]);
        } catch (\Throwable $e) {
            // намеренно игнорируем, чтобы не ломать создание врача
            logger()->warning('Telegram notify failed', [
                'error' => $e->getMessage(),
            ]);
        }

        // 🔹 ВАЖНО: JSON → модалка закрывается
        return response()->json([
            'success' => true,
            'id' => $doctor->id,
            'type' => 'doctor',
        ]);
                    // Для добавления владельца записи специалиста и доктора
        $isOwner = $request->boolean('its_me');
$user = auth()->user();

if ($isOwner && $user) {
    $doctor->owners()->syncWithoutDetaching([
        $user->id => ['is_confirmed' => false],
    ]);
}


    }

    /**
     * 🔹 Список докторов
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $cityId = null;
        $selectedCity = null;

        if ($request->filled('city_id')) {
            $cityId = (int) $request->get('city_id');

            if (!$user) {
                session(['city_id' => $cityId]);
            }
        } elseif ($user && $user->city_id) {
            $cityId = $user->city_id;
        } else {
            $cityId = session('city_id');
        }

        if ($cityId) {
            $selectedCity = City::find($cityId)?->name;
        }

        $doctors = Doctor::when($cityId, function ($query) use ($cityId) {
            $query->where('city_id', $cityId);
        })
            ->orderBy('name')
            ->get();

        return view('pages.doctors.index', compact('doctors', 'selectedCity'));
    }

    /**
     * 🔹 Доктора на главную
     */
    public function welcome()
    {
        $doctors = Doctor::orderBy('name')->limit(120)->get();
        return view('welcome', compact('doctors'));
    }

    /**
     * 🔹 Карточка врача
     */
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

    /**
     * 🔹 Обновление врача
     */
    public function update(Request $request, Doctor $doctor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'clinic' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        $data = $request->only([
            'name',
            'specialization',
            'clinic',
            'city',
            'experience',
            'description'
        ]);

        if ($request->hasFile('photo')) {
            if (!empty($doctor->photo) && \Storage::disk('public')->exists($doctor->photo)) {
                \Storage::disk('public')->delete($doctor->photo);
            }

            $data['photo'] = $request->file('photo')->store('doctors', 'public');
        }

        $doctor->update($data);

        return redirect()->back()->with('success', 'Данные врача сохранены');
    }

    /**
     * 🔹 Удаление врача
     */
    public function destroy(Doctor $doctor)
    {
        $doctor->delete();

        return redirect()
            ->route('doctors.index')
            ->with('success', 'Врач удалён');
    }
}
