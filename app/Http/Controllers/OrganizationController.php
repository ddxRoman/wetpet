<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Clinic;
use App\Models\FieldOfActivity;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\TelegramService;

class OrganizationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Получаем ПЕРВУЮ организацию пользователя
        $organization = $user->ownedOrganizations()->first();
        $hasOrganization = (bool)$organization;

        $hasClinic = $user->ownedClinics()->exists();
        $hasSpecialistProfile = $user->hasSelfSpecialist(); 
        
        $allCities = City::orderBy('name')->get();

        // Загружаем сферы деятельности раздельно
        $groupedSpecialistFields = FieldOfActivity::where('type', 'specialist')
            ->get()
            ->groupBy('category');

        $groupedOrgFields = FieldOfActivity::where('type', 'organization')
            ->get()
            ->groupBy('category');

        return view('account.index', compact(
            'user', 
            'organization', 
            'hasOrganization', 
            'hasClinic', 
            'hasSpecialistProfile', 
            'allCities', 
            'groupedSpecialistFields',
            'groupedOrgFields'
        ));
    }

    public function submit(Request $request)
    {
        $isOwner = $request->boolean('its_me');
        $user = auth()->user();

        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'city_id'              => 'required|exists:cities,id',
            'street'               => 'required|string|max:255',
            'house'                => 'required|string|max:255',
            'description'          => 'required|string',
            'logo'                 => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
            'field_of_activity_id' => 'required|exists:field_of_activities,id',
            'region'               => 'nullable|string|max:255',
            'schedule'             => 'nullable|string|max:255',
            'workdays'             => 'nullable|string|max:255',
            'phone'                => 'nullable|string|max:255',
            'email'                => 'nullable|email|max:255',
        ]);

        $activity = FieldOfActivity::find($validated['field_of_activity_id']);
        $city = City::find($validated['city_id']);
        $validated['country'] = 'Россия';

        $path = $request->hasFile('logo') 
            ? $request->file('logo')->store('organizations/logos', 'public') 
            : null;

        if ($activity->activity === "vetclinic") {
            $clinic = Clinic::create([
                'name'        => $validated['name'],
                'country'     => $validated['country'],
                'region'      => $validated['region'] ?? null,
                'city'        => $city->name,
                'street'      => $validated['street'],
                'house'       => $validated['house'],
                'description' => $validated['description'],
                'logo'        => $path,
                'schedule'    => $validated['schedule'],
                'workdays'    => $validated['workdays'],
                'phone1'      => $validated['phone'],
                'email'       => $validated['email']
            ]);

            if ($isOwner && $user) {
                $clinic->owners()->attach($user->id, ['is_confirmed' => false]);
            }

            $this->sendTelegramNotification($clinic, 'ветеринарная клиника', 'clinics');

            return response()->json(['success' => true, 'saved_to' => 'clinics', 'clinic' => $clinic]);
        }

        // Сохранение обычной организации
        $organization = Organization::create([
            'name'        => $validated['name'],
            'country'     => $validated['country'],
            'region'      => $validated['region'] ?? null,
            'city'        => $city->name,
            'street'      => $validated['street'],
            'house'       => $validated['house'],
            'description' => $validated['description'],
            'logo'        => $path,
            'schedule'    => $validated['schedule'],
            'workdays'    => $validated['workdays'],
            'phone'       => $validated['phone'],
            'email'       => $validated['email'],
            'type'        => $activity->activity,
        ]);

        if ($isOwner && $user) {
            $organization->owners()->attach($user->id, ['is_confirmed' => false]);
        }

        $this->sendTelegramNotification($organization, 'организация', 'organizations');

        return response()->json(['success' => true, 'saved_to' => 'organizations', 'organization' => $organization]);
    }

    public function update(Request $request, $id)
    {
        $organization = Organization::findOrFail($id);
        
        // Проверка прав владельца
        if (!$organization->owners()->where('user_id', auth()->id())->exists()) {
            abort(403, 'У вас нет прав на редактирование этой организации');
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'city'        => 'required|string', 
            'street'      => 'required|string',
            'house'       => 'required|string',
            'type'        => 'required|string',
            'description' => 'nullable|string',
            'phone'       => 'nullable|string',
            'email'       => 'nullable|email',
            'schedule'    => 'nullable|string',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($organization->logo) {
                Storage::disk('public')->delete($organization->logo);
            }
            $validated['logo'] = $request->file('logo')->store('organizations/logos', 'public');
        }

        $organization->update($validated);

        return back()->with('success', 'Данные организации обновлены!');
    }

    private function sendTelegramNotification($model, $label, $routePart)
    {
        $user = auth()->user();
        $url = config('app.url') . "/{$routePart}/" . ($model->slug ?? $model->id);

        $message = "<b>Новая {$label}</b>\n\n" .
                   "Название: <a href=\"{$url}\">{$model->name}</a>\n" .
                   "Город: {$model->city}\n" .
                   "Адрес: {$model->street} {$model->house}\n\n" .
                   "👤 <b>Добавил:</b> " . ($user?->name ?? 'Гость');

        try {
            app(TelegramService::class)->send($message);
        } catch (\Exception $e) {
            \Log::error("TG Error: " . $e->getMessage());
        }
    }

    public function byActivityAndCity(Request $request)
    {
        $request->validate([
            'field_of_activity_id' => 'required|exists:field_of_activities,id',
            'city_id'              => 'required|exists:cities,id',
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