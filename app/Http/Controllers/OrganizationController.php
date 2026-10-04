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

        // Исключаем ветеринарные клиники и врачей из списка сфер для организаций
        $groupedOrgFields = FieldOfActivity::where('type', 'organization')
            ->whereNotIn('activity', ['vetclinic', 'doctor'])
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

    public function catalog(Request $request)
    {
    $user = auth()->user();
    $cityId = $request->get('city_id');
    $selectedCityName = null;
    $cityModel = null;

    // 1. Приоритет выбора города: Request -> Session -> User Profile
    if (!$cityId) {
        $cityId = session('city_id') ?: ($user ? $user->city_id : null);
    }

    if ($cityId) {
        $cityModel = City::find($cityId);
        if ($cityModel) {
            $selectedCityName = $cityModel->name;
            // Сохраняем в сессию, чтобы при переходе по страницам город не терялся
            session(['city_id' => $cityId]);
        }
    }

    $selectedTypeId = $request->get('type_id');

    // Фильтр «Другие населённые пункты» (запоминается в сессии и cookie)
    $otherOnly = \App\Support\LocalityFilter::resolve($request);

    // 2. Получаем типы организаций для тегов
    $organizationTypes = FieldOfActivity::where('type', 'organization')
        ->whereNotIn('activity', ['vetclinic', 'doctor'])
        ->orderBy('name')
        ->get(['id', 'name']);

    // 2.1. Считаем количество организаций для каждого типа (и общий итог), с учётом выбранного города
    $orgCountsBaseQuery = Organization::query()
        ->forCatalog($cityModel, $otherOnly);

    $totalOrganizationsCount = (clone $orgCountsBaseQuery)->count();

    $organizationTypeCounts = (clone $orgCountsBaseQuery)
        ->whereNotNull('field_of_activity_id')
        ->selectRaw('field_of_activity_id, COUNT(*) as aggregate')
        ->groupBy('field_of_activity_id')
        ->pluck('aggregate', 'field_of_activity_id');

    $organizationTypes->each(function ($type) use ($organizationTypeCounts) {
        $type->count = $organizationTypeCounts->get($type->id, 0);
    });

    // 3. Запрос организаций
    $items = Organization::query()
        // Город выбранный + «другие населённые пункты» его региона (или только они — по фильтру)
        ->forCatalog($cityModel, $otherOnly)
        ->when($selectedTypeId, function ($q) use ($selectedTypeId) {
            $q->where('field_of_activity_id', $selectedTypeId);
        })
        ->withCount('reviews') // Для бейджа рейтинга
        ->withAvg('reviews', 'rating') // Для звезд
        ->with(['promotions' => fn($q) => $q->active(), 'fieldOfActivity'])
        ->localFirst($cityModel)
        ->orderBy('name')
        ->paginate(16);

    // Если это AJAX запрос (нажата кнопка "Показать еще")
    if ($request->ajax()) {
        return view('pages.organizations._list_items', ['organizations' => $items])->render();
    }

    // SEO: отдельные редактируемые шаблоны для каталога и для фильтра по типу деятельности
    $seoManager = new \App\Services\SeoManager();
    $seoVars = ['city' => $selectedCityName];
    if ($selectedTypeId) {
        $activityTypeName = $organizationTypes->firstWhere('id', (int) $selectedTypeId)?->name;
        $seoMeta = $seoManager->getCatalogMeta('organizations_activity', $seoVars + ['activity_type' => $activityTypeName]);
    } else {
        $seoMeta = $seoManager->getCatalogMeta('organizations', $seoVars);
    }

    return view('pages.organizations.index', [
        'organizations' => $items,
        'selectedCity' => $selectedCityName,
        'organizationTypes' => $organizationTypes,
        'selectedTypeId' => $selectedTypeId,
        'currentCityId' => $cityId,
        'seoMeta' => $seoMeta,
        'totalOrganizationsCount' => $totalOrganizationsCount,
        'otherOnly' => $otherOnly,
    ]);
    }

        public function submit(Request $request)
    {
        // Защита от двойной отправки формы (двойной клик, повторный запрос)
        return \App\Support\DuplicateSubmissionGuard::run($request, 'organization', fn () => $this->performSubmit($request));
    }

private function performSubmit(Request $request)
    {
    $isOwner = $request->boolean('its_me');
    $user = auth()->user();

    $validated = $request->validate([
        'name'                 => 'required|string|max:255',
        'city_id'              => 'nullable|exists:cities,id',
        // Город: выбранный из списка (city_id) или введённый вручную (city_name + region)
        'city_name'            => 'nullable|string|max:120',
        'region'               => 'nullable|string|max:255',
        'street'               => 'required|string|max:255',
        'house'                => 'required|string|max:255',
        'description'          => 'nullable|string',
        'logo'                 => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
        'field_of_activity_id' => 'required|exists:field_of_activities,id',
        'schedule'             => 'nullable|string|max:255',
        'workdays'             => 'nullable|string|max:255',
        'phone1'               => 'nullable|string|max:255',
        'phone2'               => 'nullable|string|max:255',
        'email'                => 'nullable|email|max:255',
        'personal_data_agreement' => 'required|accepted',
    ], [
        'personal_data_agreement.required' => 'Необходимо согласие на обработку персональных данных.',
        'personal_data_agreement.accepted' => 'Необходимо согласие на обработку персональных данных.',
    ]);

    // Проверка на дубли (до создания города и карточки): «Мы нашли похожую клинику/организацию. Это она?»
    // Пользователь ответил «Нет, создать новую» — форма уходит повторно с skip_duplicates = 1.
    if (! $request->boolean('skip_duplicates')) {
        $duplicates = app(\App\Services\DuplicateFinder::class)->forOrganization([
            'name'      => $validated['name'],
            'city_id'   => $request->input('city_id'),
            'city_name' => $request->input('city_name'),
            'street'    => $validated['street'],
            'house'     => $validated['house'],
            'phone1'    => $validated['phone1'] ?? null,
            'phone2'    => $validated['phone2'] ?? null,
        ]);

        if ($duplicates) {
            return \App\Services\DuplicateFinder::conflict($duplicates);
        }
    }

    $activity = FieldOfActivity::find($validated['field_of_activity_id']);
    // Город из списка или введённый вручную (если его нет в базе — создаётся с large_city = 0)
    $city = app(\App\Services\CityResolver::class)->fromRequest($request);

    $path = $request->hasFile('logo') 
        ? $request->file('logo')->store('organizations/logos', 'public') 
        : null;

    $data = [
        'name'                 => $validated['name'],
        'field_of_activity_id' => $validated['field_of_activity_id'],
        'country'              => 'Россия',
        'region'               => $city->region,
        'city'                 => $city->name,
        'street'               => $validated['street'],
        'house'                => $validated['house'],
        'description'          => $validated['description'],
        'logo'                 => $path,
        'schedule'             => $validated['schedule'],
        'workdays'             => $validated['workdays'],
        'phone1'               => $validated['phone1'],
        'phone2'               => $validated['phone2'],
        'email'                => $validated['email'],
    ];

    if ($activity->activity === "vetclinic") {
        $model = Clinic::create($data);
        $type = 'clinics';
    } else {
        // Убрали строку $data['type'] = $activity->activity; так как колонки нет
        $model = Organization::create($data);
        $type = 'organizations';
    }

    if ($isOwner && $user) {
        $model->owners()->attach($user->id, ['is_confirmed' => false]);
    }

    $this->sendTelegramNotification($model, ($type == 'clinics' ? 'клиника' : 'организация'), $type, $city);

$successMessage = $type === 'clinics'
    ? 'Клиника успешно добавлена!'
    : 'Организация успешно добавлена!';

// Адрес только что созданной карточки
$redirectUrl = $type === 'clinics'
    ? route('clinics.show', ['city' => $model->city_slug, 'clinic' => $model->slug])
    : route('organizations.show', ['city' => $model->city_slug, 'slug' => $model->slug]);

// «Это моя организация»: права на управление появятся только после проверки документов —
// сразу ведём на страницу заявки, где просят загрузить документы
if ($isOwner && $user) {
    $redirectUrl = $type === 'clinics'
        ? route('owner.clinic', $model->id)
        : route('owner.organization', $model->id);

    $successMessage .= ' Загрузите документы, подтверждающие право владения, — после проверки вы получите доступ к управлению.';
    session()->flash('success', $successMessage);
}

if ($request->ajax() || $request->expectsJson()) {
    return response()->json([
        'success'      => true,
        'type'         => $type === 'clinics' ? 'clinic' : 'organization',
        'message'      => $successMessage,
        'redirect_url' => $redirectUrl,
    ]);
}

return redirect()->to($redirectUrl)->with('success', $successMessage);
    }

    public function show($city, $slug)
    {
    $organization = Organization::with(['activityType'])
        ->withCount('reviews') // Теперь будет искать по reviewable_id
        ->withAvg('reviews', 'rating')
        ->where('slug', $slug)
        ->first();

    // Карточку могли перенести в «Клиники» (сфера деятельности «Ветеринарная клиника») —
    // старую ссылку /organizations/... ведём на новую /clinics/... (slug сохраняется).
    if (!$organization) {
        $clinic = Clinic::where('slug', $slug)->first();
        if ($clinic) {
            return redirect()->route('clinics.show', ['city' => $clinic->city_slug, 'clinic' => $clinic->slug], 301);
        }
        abort(404);
    }

    // Каноническая ссылка вида /organizations/{city}/{slug}: если сегмент города
    // в URL не совпадает с актуальным городом организации — редиректим на верный адрес.
    if ($city !== $organization->city_slug) {
        return redirect()->route('organizations.show', ['city' => $organization->city_slug, 'slug' => $organization->slug], 301);
    }

    return view('pages.organizations.show', compact('organization'));
    }


    public function update(Request $request, $id)
    {
    $organization = Organization::findOrFail($id);

    // Редактировать может только подтверждённый владелец
    \App\Support\OwnerAccess::authorizeConfirmed('organization', (int) $organization->id);

    $validated = $request->validate([
        'name'                 => 'required|string|max:255',
        'field_of_activity_id' => 'required|exists:field_of_activities,id', 
        'city'                 => 'required|string', 
        'street'               => 'required|string',
        'house'                => 'required|string',
        'type'                 => 'required|string',
        'description'          => 'nullable|string',
        'phone1'               => 'nullable|string',
        'phone2'               => 'nullable|string',
        'email'                => 'nullable|email',
        'schedule'             => 'nullable|string',
        'workdays'             => 'nullable|string',
        'logo'                 => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);

    if ($request->hasFile('logo')) {
        if ($organization->logo) {
            Storage::disk('public')->delete($organization->logo);
        }
        $validated['logo'] = $request->file('logo')->store('organizations/logos', 'public');
    }

    // Модель сама пересчитает slug в событии updating, так как поля изменились
    $organization->update($validated);

    return redirect()->to(url('/account') . '#my-organizations')
        ->with('success', 'Данные организации обновлены!');
    }

    public function destroy($id)
    {
        $organization = Organization::findOrFail($id);

        // Удалять может только подтверждённый владелец
        \App\Support\OwnerAccess::authorizeConfirmed('organization', (int) $organization->id, 'У вас нет прав на удаление этой организации');

        if ($organization->logo) {
            Storage::disk('public')->delete($organization->logo);
        }

        $organization->delete();

        // Редирект на личный кабинет с якорем на организации
        return redirect()->to(url('/account') . '#my-organizations')
            ->with('success', 'Организация успешно удалена');
    }

    private function sendTelegramNotification($model, $label, $routePart, ?City $city = null)
    {
        $user = auth()->user();
        $url = config('app.url') . "/{$routePart}/{$model->city_slug}/" . ($model->slug ?? $model->id);

        $message = "<b>Новая {$label}</b>\n\n" .
                   \App\Services\CityResolver::newCityNote($city) .
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