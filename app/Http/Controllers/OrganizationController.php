<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Clinic;
use App\Models\FieldOfActivity;
use App\Models\City;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function submit(Request $request)
    {
        // ------------------------------
        // ВАЛИДАЦИЯ
        // ------------------------------
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'country'              => 'nullable|string|max:255',
            'region'               => 'nullable|string|max:255',
            'city_id'              => 'required|integer',
            'street'               => 'required|string|max:255',
            'house'                => 'nullable|string|max:255',
            'description'          => 'nullable|string',
            'logo'                 => 'nullable|string',
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
        $logo     = $request->input('logo', null);
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
                'logo'        => $logo,
                'schedule'    => $schedule,
                'workdays'    => $workdays,
                'phone1'      => $phone,   // В модели Clinics: phone1 + phone2
                'email'       => $email
            ]);

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
            'logo'        => $logo,
            'schedule'    => $schedule,
            'workdays'    => $workdays,
            'phone'       => $phone,
            'email'       => $email,
            'type'        => $activity->activity, // Например "Груминг", "Зоомагазин"
        ]);

        return response()->json([
            'success' => true,
            'saved_to' => 'organizations',
            'organization' => $organization,
        ]);
    }
}
