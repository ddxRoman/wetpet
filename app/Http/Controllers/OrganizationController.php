<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Clinic;
use App\Models\FieldOfActivity;
use App\Models\City;
use Illuminate\Http\Request;
use App\Services\TelegramService;


class OrganizationController extends Controller
{
    public function submit(Request $request)
    {

        $isOwner = $request->boolean('its_me');
$user = auth()->user();


        // ------------------------------
        // ВАЛИДАЦИЯ
        // ------------------------------
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'country'              => 'nullable|string|max:255',
            'region'               => 'nullable|string|max:255',
            'city_id'              => 'required|integer',
            'street'               => 'required|string|max:255',
            'house'                => 'required|string|max:255',
            'description'          => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',

            'schedule'             => 'nullable|string|max:255',
            'workdays'             => 'nullable|string|max:255',
            'phone'                => 'nullable|string|max:255',
            'email'                => 'nullable|string|max:255',
            'field_of_activity_id' => 'required|integer'
        ]);

        // ------------------------------
        // ПОЛУЧАЕМ НУЖНУЮ СФЕРУ
        // ------------------------------
        $activity = FieldOfActivity::find($validated['field_of_activity_id']);

        if (!$activity) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid field_of_activity_id'
            ], 422);
        }

        // ------------------------------
        // УСТАНАВЛИВАЕМ СТРАНУ ПО УМОЛЧАНИЮ
        // ------------------------------
 $validated['country'] = 'Россия';

if ($request->hasFile('logo')) {
$path = $request->file('logo')->store('clinics/logo', 'public');

} else {
    $path = null;
}

        // ------------------------------
        // ГОРОД ПО ID
        // ------------------------------
        $city = City::find($validated['city_id']);

        if (!$city) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid city_id'
            ], 422);
        }

        // ------------------------------
        // SAFE GET ДЛЯ НЕОБЯЗАТЕЛЬНЫХ ПОЛЕЙ
        // ------------------------------

        $schedule = $request->input('schedule', null);
        $workdays = $request->input('workdays', null);
        $phone    = $request->input('phone', null);
        $email    = $request->input('email', null);
        $desc     = $request->input('description', null);

        // =============================================================================
        // CASE 1 — ЕСЛИ "Ветеринарная клиника" → СОХРАНЯЕМ В ΤΑΒLICS CLINICS
        // =============================================================================
        if ($activity->activity === "vetclinic") {

            $clinic = Clinic::create([
                'name'        => $validated['name'],
                'country'     => $validated['country'],
                'region'      => $validated['region'] ?? null,
                'city'        => $city->name,
                'street'      => $validated['street'],
                'house'       => $validated['house'] ?? null,
                'description' => $desc,
                'logo'        => $path,
                'schedule'    => $schedule,
                'workdays'    => $workdays,
                'phone1'      => $phone,   // В модели Clinics: phone1 + phone2
                'email'       => $email
            ]);

                    if ($isOwner && $user) {
    $clinic->owners()->attach($user->id, [
        'is_confirmed' => false, // позже можно сделать подтверждение
    ]);
}

                $user = auth()->user();

$clinicUrl = config('app.url') . '/clinics/' . $clinic->slug;

$message =
    "🏥 <b>Новая ветеринарная клиника</b>\n\n" .
    "Название: <a href=\"{$clinicUrl}\">{$clinic->name}</a>\n" .
    "Город: {$clinic->city}\n" .
    "Адрес: {$clinic->street} {$clinic->house}\n\n" .
    "👤 <b>Добавил:</b>\n" .
    "Имя: " . ($user?->name ?? 'Гость') . "\n" .
    "Email: " . ($user?->email ?? '—');

app(TelegramService::class)->send($message);




            return response()->json([
                'success' => true,
                'saved_to' => 'clinics',
                'clinic' => $clinic,
            ]);
        }

        // =============================================================================
        // CASE 2 — ЛЮБАЯ ДРУГАЯ ДЕЯТЕЛЬНОСТЬ → СОХРАНЯЕМ В ORGANIZATIONS
        // =============================================================================
        $organization = Organization::create([
            'name'        => $validated['name'],
            'country'     => $validated['country'],
            'region'      => $validated['region'] ?? null,
            'city'        => $city->name,
            'street'      => $validated['street'],
            'house'       => $validated['house'] ?? null,
            'description' => $desc,
            'logo'        => $path,
            'schedule'    => $schedule,
            'workdays'    => $workdays,
            'phone'       => $phone,
            'email'       => $email,
            'type'        => $activity->activity, // Например "Груминг", "Зоомагазин"
        ]);

        if ($isOwner && $user) {
    $organization->owners()->attach($user->id, [
        'is_confirmed' => false, // позже можно сделать подтверждение
    ]);
}

            // 🔔 TELEGRAM — ДО return
    $user = auth()->user();

$orgUrl = config('app.url') . '/organizations/' . $organization->slug;

$message =
    "🏢 <b>Новая организация</b>\n\n" .
    "Название: <a href=\"{$orgUrl}\">{$organization->name}</a>\n" .
    "Город: {$organization->city}\n" .
    "Адрес: {$organization->street} {$organization->house}\n\n" .
    "👤 <b>Добавил:</b>\n" .
    "Имя: " . ($user?->name ?? 'Гость') . "\n" .
    "Email: " . ($user?->email ?? '—');

app(TelegramService::class)->send($message);



        return response()->json([
            'success' => true,
            'saved_to' => 'organizations',
            'organization' => $organization,
        ]);
    }

    public function byActivityAndCity(Request $request)
{
    $request->validate([
        'field_of_activity_id' => 'required|exists:field_of_activities,id',
        'city_id' => 'required|exists:cities,id',
    ]);

    $activity = FieldOfActivity::find($request->field_of_activity_id)->activity;
    $cityName = City::find($request->city_id)->name;

    $organizations = Organization::where('type', $activity)
        ->where('city', $cityName)
        ->orderBy('name')
        ->get(['id', 'name']);

    return response()->json($organizations);
}



}
