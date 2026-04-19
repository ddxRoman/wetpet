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
    // 1. Валидация (добавил недостающие поля из вашей формы)
    $validated = $request->validate([
        'name'                 => 'required|string|max:255',
        'field_of_activity_id' => 'required|exists:field_of_activities,id',
        'city_id'              => 'nullable|exists:cities,id',
        'clinic_id'            => 'nullable|exists:clinics,id',
        'experience'           => 'nullable|string|max:255',
        'description'          => 'nullable|string',
        'exotic_animals'       => 'nullable|string', // Добавлено
        'On_site_assistance'   => 'nullable|string', // Добавлено
        'phone'                => 'nullable|string|max:255', // Для таблицы контактов
        'mail'                 => 'nullable|string|email|max:255', // Для таблицы контактов
        'messengers'           => 'nullable|array', // Для таблицы контактов
    ]);

    // 🔹 Получаем специализацию
    $field = FieldOfActivity::findOrFail($validated['field_of_activity_id']);

    // 🔹 Создаём врача
    $doctor = Doctor::create([
        'name'                 => $validated['name'],
        'specialization'       => $field->name,
        'field_of_activity_id' => $field->id,
        'city_id'              => $validated['city_id'] ?? null,
        'clinic_id'            => $validated['clinic_id'] ?? null,
        'experience'           => $validated['experience'] ?? null,
        'description'          => $validated['description'] ?? null,
        'exotic_animals'       => $request->exotic_animals ?? 'Нет',
        'On_site_assistance'   => $request->On_site_assistance ?? 'Нет',
        'slug'                 => Str::slug($validated['name']) . '-' . rand(100, 999),
    ]);

    /* ============================================================
       🔥 СОХРАНЕНИЕ КОНТАКТОВ (doctor_contacts)
    ============================================================ */
    $telegram = ($request->messengers && in_array('telegram', $request->messengers)) ? $request->phone : null;
    $whatsapp = ($request->messengers && in_array('whatsapp', $request->messengers)) ? $request->phone : null;
    $max      = ($request->messengers && in_array('messenger', $request->messengers)) ? $request->phone : null;

    $doctor->contacts()->create([
        'phone'    => $request->phone,
        'email'    => $request->mail, // в форме поле называется mail
        'telegram' => $telegram,
        'whatsapp' => $whatsapp,
        'max'      => $max,
    ]);

    // 🔹 Уведомление в Telegram (оставляем вашу логику)
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
        logger()->warning('Telegram notify failed', ['error' => $e->getMessage()]);
    }

    // Для добавления владельца (ваша логика "Это я")
    $isOwner = $request->boolean('its_me');
    $user = auth()->user();

    if ($isOwner && $user) {
        $doctor->owners()->syncWithoutDetaching([
            $user->id => ['is_confirmed' => false],
        ]);
    }

    // 🔹 ВАЖНО: JSON → модалка закрывается
    return response()->json([
        'success' => true,
        'id' => $doctor->id,
        'type' => 'doctor',
    ]);
}

/**
     * 🔹 Список докторов с сортировкой по рейтингу
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

        // Подгружаем средний рейтинг и сортируем
        $doctors = Doctor::withAvg('reviews', 'rating')
            ->when($cityId, function ($query) use ($cityId) {
                $query->where('city_id', $cityId);
            })
            ->orderByDesc('reviews_avg_rating') // Топ по рейтингу в начале
            ->orderBy('name') // Если рейтинг одинаковый — по алфавиту
            ->get();

        return view('pages.doctors.index', compact('doctors', 'selectedCity'));
    }

    /**
     * 🔹 Доктора на главную
     */
public function welcome()
{
    $doctors = Doctor::orderBy('name')->limit(120)->get();

    $canAddSelf = true;

    if (auth()->check()) {
        $user = auth()->user();

        $canAddSelf = !(
            $user->ownedDoctors()->exists() ||
            $user->ownedSpecialists()->exists()
        );
    }

    return view('welcome', compact('doctors', 'canAddSelf'));
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
    // 1. Валидация
    $validated = $request->validate([
        'name'           => 'required|string|max:255',
        'specialization' => 'nullable|string|max:255',
        'city_id'        => 'nullable|exists:cities,id',
        'organization_id'=> 'nullable|exists:clinics,id',
        'experience'     => 'nullable|integer|min:0',
        'date_of_birth'  => 'nullable|date',
        'description'    => 'nullable|string',
        'photo'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
        'phone'          => 'nullable|string|max:20',
        'email'          => 'nullable|email|max:255',
        'telegram'       => 'nullable|string|max:255',
        'whatsapp'       => 'nullable|string|max:255',
        'max'            => 'nullable|string|max:255',
        'exotic_animals' => 'nullable|string',
        'On_site_assistance' => 'nullable|string',
    ]);

    // 2. Подготовка данных для doctors
    $doctorData = $request->only([
        'name', 'specialization', 'city_id', 'organization_id', 
        'experience', 'description', 'date_of_birth',
        'exotic_animals', 'On_site_assistance'
    ]);

    // 3. Обработка фото
    if ($request->hasFile('photo')) {
        if (!empty($doctor->photo) && \Storage::disk('public')->exists($doctor->photo)) {
            \Storage::disk('public')->delete($doctor->photo);
        }
        $doctorData['photo'] = $request->file('photo')->store('doctors', 'public');
    }

    $doctor->update($doctorData);

    // 4. Обновление контактов
    $doctor->contacts()->updateOrCreate(
        ['doctor_id' => $doctor->id],
        [
            'phone'    => $request->phone,
            'email'    => $request->email,
            'telegram' => $request->telegram,
            'whatsapp' => $request->whatsapp,
            'max'      => $request->max,
        ]
    );

    // Редирект на конкретный URL с хэшем (анкором)
    return redirect()->to(url('/account') . '#doctor-profile')
        ->with('success', 'Данные врача сохранены');
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
