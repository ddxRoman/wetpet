<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\ClinicOwner;
use App\Models\Organization;
use App\Models\OrganizationOwner;
use App\Models\Doctor;
use App\Models\DoctorOwner;
use App\Models\Specialist;
use App\Models\SpecialistOwner;
use App\Models\Service;
use App\Models\FieldOfActivity;
use App\Models\Price;
use App\Services\EntityTypeConverter;
use App\Models\EntityPhoto;
use App\Models\OwnerFeedback;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OwnerCabinetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ══════════════════════════════════════════════════════════
    //  ОПРЕДЕЛЯЕМ КАБИНЕТ ПОЛЬЗОВАТЕЛЯ
    // ══════════════════════════════════════════════════════════

    /**
     * Главная страница кабинета — редирект на нужный тип
     */
/**
     * Главная страница кабинета — редирект на нужный тип или вывод документов
     */
/**
     * Главная страница кабинета — редирект на нужный тип или вывод документов для проверки
     */
   // ══════════════════════════════════════════════════════════
    //  ОПРЕДЕЛЯЕМ КАБИНЕТ ПОЛЬЗОВАТЕЛЯ
    // ══════════════════════════════════════════════════════════

    /**
     * Главная страница кабинета
     */
    public function index()
    {
        $user = Auth::user();
        $this->purgeOrphanedOwnerships();

        // 1. Получаем вообще все привязанные сущности (и подтвержденные, и нет)
        $allUserEntities = $this->getAllUserEntities();

        if ($allUserEntities->isEmpty()) {
            return redirect()->route('account')->with('info', 'У вас пока нет зарегистрированных организаций или кабинетов.');
        }

        // 2. Сначала ищем ХОТЯ БЫ ОДНУ подтвержденную сущность, чтобы пустить пользователя в работу
        if ($owner = ClinicOwner::where('user_id', $user->id)->where('is_confirmed', true)->first()) {
            return redirect()->route('owner.clinic', $owner->clinic_id);
        }
        if ($owner = OrganizationOwner::where('user_id', $user->id)->where('is_confirmed', true)->first()) {
            return redirect()->route('owner.organization', $owner->organization_id);
        }
        if ($owner = DoctorOwner::where('user_id', $user->id)->where('is_confirmed', true)->first()) {
            return redirect()->route('owner.doctor', $owner->doctor_id);
        }
        if ($owner = SpecialistOwner::where('user_id', $user->id)->where('is_confirmed', true)->first()) {
            return redirect()->route('owner.specialist', $owner->specialist_id);
        }

        // 3. Если подтвержденных вообще НЕТ, тогда собираем только неисполненные для страницы no-access
        $pendingOwners = $this->getPendingOwners();
        return view('pages.owner.no-access', compact('pendingOwners', 'allUserEntities'));
    }



    // ══════════════════════════════════════════════════════════
    //  ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ (Добавь их в контроллер)
    // ══════════════════════════════════════════════════════════

    /**
     * Получить абсолютно все сущности пользователя
     */
    /**
     * Карта: тип объекта => [модель владельца, FK-колонка, связь на сам объект, модель объекта].
     */
    private function ownerMap(): array
    {
        return [
            'clinic'       => [ClinicOwner::class,       'clinic_id',       'clinic',       Clinic::class],
            'organization' => [OrganizationOwner::class, 'organization_id', 'organization', Organization::class],
            'doctor'       => [DoctorOwner::class,       'doctor_id',       'doctor',       Doctor::class],
            'specialist'   => [SpecialistOwner::class,   'specialist_id',   'specialist',   Specialist::class],
        ];
    }

    /**
     * Удаляет у текущего пользователя записи владения, чей объект
     * (клиника/организация/врач/специалист) уже не существует в БД.
     */
    private function purgeOrphanedOwnerships(): void
    {
        $userId = Auth::id();
        if (!$userId) return;

        foreach ($this->ownerMap() as [$ownerModel, , $relation]) {
            $ownerModel::where('user_id', $userId)
                ->whereDoesntHave($relation)
                ->get()
                ->each(function ($row) {
                    $row->documents()->delete();
                    $row->messages()->delete();
                    $row->delete();
                });
        }
    }

    /**
     * Если объекта нет в БД — чистим «висящую» запись владения и показываем
     * страницу «Объект не найден». Если записи владения нет вовсе — обычный 404.
     * Возвращает Response|null (null — объект существует, можно продолжать).
     */
    private function missingEntityResponse(string $type, int $id)
    {
        [$ownerModel, $fk, , $entityModel] = $this->ownerMap()[$type];

        if ($entityModel::whereKey($id)->exists()) {
            return null;
        }

        $rows = $ownerModel::where('user_id', Auth::id())->where($fk, $id)->get();
        if ($rows->isEmpty()) {
            abort(404);
        }

        $rows->each(function ($row) {
            $row->documents()->delete();
            $row->messages()->delete();
            $row->delete();
        });

        return response()->view('pages.owner.entity-missing', [
            'type'            => $type,
            'allUserEntities' => $this->getAllUserEntities(),
        ], 404);
    }

    private function getAllUserEntities()
    {
        $this->purgeOrphanedOwnerships();

        $user = Auth::user();
        $entities = collect();

        foreach (ClinicOwner::with('clinic')->where('user_id', $user->id)->get() as $row) {
            $entities->push([
                'id' => $row->clinic_id, 'type' => 'clinic', 'name' => $row->clinic?->name ?? 'Клиника',
                'address' => $this->entityAddressLine($row->clinic), 'is_confirmed' => $row->is_confirmed, 'icon' => '🏥'
            ]);
        }
        foreach (OrganizationOwner::with('organization')->where('user_id', $user->id)->get() as $row) {
            $entities->push([
                'id' => $row->organization_id, 'type' => 'organization', 'name' => $row->organization?->name ?? 'Организация',
                'address' => $this->entityAddressLine($row->organization), 'is_confirmed' => $row->is_confirmed, 'icon' => '🏢'
            ]);
        }
        foreach (DoctorOwner::with('doctor.city')->where('user_id', $user->id)->get() as $row) {
            $entities->push([
                'id' => $row->doctor_id, 'type' => 'doctor', 'name' => $row->doctor?->name ?? 'Врач',
                'address' => $this->entityAddressLine($row->doctor), 'is_confirmed' => $row->is_confirmed, 'icon' => '👨‍⚕️'
            ]);
        }
        foreach (SpecialistOwner::with('specialist.city')->where('user_id', $user->id)->get() as $row) {
            $entities->push([
                'id' => $row->specialist_id, 'type' => 'specialist', 'name' => $row->specialist?->name ?? 'Специалист',
                'address' => $this->entityAddressLine($row->specialist), 'is_confirmed' => $row->is_confirmed, 'icon' => '🩺'
            ]);
        }

        return $entities;
    }

    /**
     * Получить только сущности на модерации
     */
    /**
     * Короткий адрес объекта для плашки-переключателя кабинетов.
     * У клиник/организаций город хранится строкой; у врачей/специалистов — связью с cities.
     */
    private function entityAddressLine($entity): ?string
    {
        if (!$entity) {
            return null;
        }

        if ($entity instanceof Clinic || $entity instanceof Organization) {
            $parts = [$entity->city, $entity->street, $entity->house];
        } else {
            // Doctor / Specialist
            $cityName = $entity->city?->name;
            $parts = [$cityName, $entity->street ?? null, $entity->house ?? null];
        }

        $line = implode(', ', array_filter($parts, fn ($p) => filled($p)));

        return $line !== '' ? $line : null;
    }


    private function getPendingOwners()
    {
        $this->purgeOrphanedOwnerships();

        $user = Auth::user();
        $pending = collect();

        foreach (ClinicOwner::where('user_id', $user->id)->where('is_confirmed', false)->get() as $row) {
            $pending->push(['owner_row' => $row, 'entity_type' => 'clinic', 'entity_name' => $row->clinic?->name ?? 'Клиника', 'icon' => '🏥']);
        }
        foreach (OrganizationOwner::where('user_id', $user->id)->where('is_confirmed', false)->get() as $row) {
            $pending->push(['owner_row' => $row, 'entity_type' => 'organization', 'entity_name' => $row->organization?->name ?? 'Организация', 'icon' => '🏢']);
        }
        foreach (DoctorOwner::where('user_id', $user->id)->where('is_confirmed', false)->get() as $row) {
            $pending->push(['owner_row' => $row, 'entity_type' => 'doctor', 'entity_name' => $row->doctor?->name ?? 'Врач', 'icon' => '👨‍⚕️']);
        }
        foreach (SpecialistOwner::where('user_id', $user->id)->where('is_confirmed', false)->get() as $row) {
            $pending->push(['owner_row' => $row, 'entity_type' => 'specialist', 'entity_name' => $row->specialist?->name ?? 'Специалист', 'icon' => '🩺']);
        }

        return $pending;
    }

    /**
     * Найти owner-запись пользователя для конкретного объекта (по типу и id).
     * Возвращает [$ownerRow, $icon] или [null, null], если записи нет.
     */
    private function getOwnerRowFor(string $type, int $id): array
    {
        $user = Auth::user();

        $map = [
            'clinic'       => [ClinicOwner::class,       'clinic_id',       '🏥'],
            'organization' => [OrganizationOwner::class, 'organization_id', '🏢'],
            'doctor'       => [DoctorOwner::class,        'doctor_id',       '👨‍⚕️'],
            'specialist'   => [SpecialistOwner::class,    'specialist_id',   '🩺'],
        ];

        if (!isset($map[$type])) {
            return [null, null];
        }

        [$modelClass, $foreignKey, $icon] = $map[$type];

        $ownerRow = $modelClass::where('user_id', $user->id)->where($foreignKey, $id)->first();

        return [$ownerRow, $icon];
    }


    // ══════════════════════════════════════════════════════════
    //  КЛИНИКА
    // ══════════════════════════════════════════════════════════

    public function clinic(int $id)
    {
        if ($missing = $this->missingEntityResponse('clinic', $id)) {
            return $missing;
        }

        $this->authorizeOwner('clinic', $id);

        // Если заявка на этот объект ещё не подтверждена — показываем
        // страницу с загрузкой документов и чатом вместо полного кабинета.
        [$ownerRow, $icon] = $this->getOwnerRowFor('clinic', $id);
        if ($ownerRow && !$ownerRow->is_confirmed) {
            $clinic = Clinic::findOrFail($id);
            return view('pages.owner.pending', [
                'ownerRow'        => $ownerRow,
                'entityName'      => $clinic->name,
                'icon'            => $icon,
                'entityId'        => $id,
                'type'            => 'clinic',
                'allUserEntities' => $this->getAllUserEntities(),
            ]);
        }

        $clinic = Clinic::with(['services', 'prices.service', 'doctors', 'awards'])->findOrFail($id);
        $photos = EntityPhoto::where('photoable_type', Clinic::class)->where('photoable_id', $id)
                        ->orderBy('sort_order')->get();

        // Клиника — это весь спектр ветеринарных услуг (все специализации врачей)
        $doctorActivityNames = FieldOfActivity::where('type', 'specialist')
            ->where('activity', 'doctor')
            ->pluck('name');

        // + услуги, помеченные направлением «Ветеринарная клиника» в поле «Специализация организации»
        $relevantServices = Service::where(function ($q) use ($doctorActivityNames) {
                $q->whereIn('specialization_doctor', $doctorActivityNames)
                  ->orWhere('specialization', FieldOfActivity::VET_CLINIC_NAME);
            })
            ->orderBy('name')->get()->unique('name')->values();

        $allServices = Service::orderBy('name')->get()->unique('name')->values();

        $allUserEntities = $this->getAllUserEntities();

        return view('pages.owner.clinic', compact('clinic', 'photos', 'relevantServices', 'allServices', 'allUserEntities'));
    }

    public function updateClinic(Request $request, int $id)
    {
        $this->authorizeOwner('clinic', $id);
        $clinic = Clinic::findOrFail($id);

        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'country'         => 'required|string|max:255',
            'region'          => 'nullable|string|max:255',
            'city'            => 'required|string|max:255',
            'street'          => 'required|string|max:255',
            'house'           => 'nullable|string|max:50',
            'address_comment' => 'nullable|string|max:500',
            'phone1'          => 'nullable|string|max:30',
            'phone2'          => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:255',
            'website'         => 'nullable|url|max:255',
            'telegram'        => 'nullable|string|max:100',
            'whatsapp'        => 'nullable|string|max:100',
            'max'             => 'nullable|string|max:100',
            'schedule'        => 'nullable|string|max:255',
            'workdays'        => 'nullable|string|max:255',
            'seo_title'       => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:320',
            'field_of_activity_id' => 'nullable|exists:field_of_activities,id',
        ]);

        // Поле сферы деятельности в таблице clinics не хранится — это только триггер переноса.
        $fieldId = $data['field_of_activity_id'] ?? null;
        unset($data['field_of_activity_id']);

        if ($request->hasFile('logo')) {
            $request->validate(['logo' => 'image|mimes:jpeg,png,jpg,webp|max:2048']);
            if ($clinic->logo) Storage::disk('public')->delete($clinic->logo);
            $data['logo'] = $request->file('logo')->store('clinics/logos', 'public');
        } elseif ($request->boolean('remove_logo')) {
            if ($clinic->logo) Storage::disk('public')->delete($clinic->logo);
            $data['logo'] = null;
        }

        // Slug не запрашивается у пользователя — сохраняем текущий,
        // либо генерируем из названия, если его почему-то ещё нет.
        if (empty($clinic->slug)) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']) . '-' . $id;
        }

        $clinic->update($data);

        // Выбрана сфера деятельности, отличная от «Ветеринарная клиника» —
        // карточка переезжает из «Клиник» в «Организации» (меняется и URL).
        if ($fieldId) {
            $field = FieldOfActivity::find($fieldId);

            if ($field && !$field->isVetClinic()) {
                $organization = app(EntityTypeConverter::class)
                    ->clinicToOrganization($clinic->fresh(), (int) $field->id);

                return redirect()
                    ->route('owner.organization', ['id' => $organization->id, 'tab' => 'info'])
                    ->with('success', 'Сфера деятельности изменена: карточка перенесена в раздел «Организации».');
            }
        }

        return back()->with('success', 'Данные клиники обновлены');
    }

    // ══════════════════════════════════════════════════════════
    //  ОРГАНИЗАЦИЯ
    // ══════════════════════════════════════════════════════════

public function organization(int $id)
    {
        // 1. Проверяем права (доступно только если организация подтверждена)
        if ($missing = $this->missingEntityResponse('organization', $id)) {
            return $missing;
        }

        $this->authorizeOwner('organization', $id);

        // Если заявка на этот объект ещё не подтверждена — показываем
        // страницу с загрузкой документов и чатом вместо полного кабинета.
        [$ownerRow, $icon] = $this->getOwnerRowFor('organization', $id);
        if ($ownerRow && !$ownerRow->is_confirmed) {
            $organization = Organization::findOrFail($id);
            return view('pages.owner.pending', [
                'ownerRow'        => $ownerRow,
                'entityName'      => $organization->name,
                'icon'            => $icon,
                'entityId'        => $id,
                'type'            => 'organization',
                'allUserEntities' => $this->getAllUserEntities(),
            ]);
        }

        // 2. Выбираем данные организации, фото и список услуг
        $organization = Organization::with(['prices.service', 'activityType'])->findOrFail($id);
        $photos = EntityPhoto::where('photoable_type', Organization::class)
            ->where('photoable_id', $id)
            ->orderBy('sort_order')
            ->get();

        // Услуги, relevant конкретно для сферы деятельности этой организации
        // (FieldOfActivity.name совпадает с Service.specialization_doctor по значению)
        $activityName = $organization->activityType->name ?? null;

        // Услуги организации подбираются по её направлению:
        //  - «Специализация организации» услуги = направление организации (например, «Ветаптека»);
        //  - «Специализация врача» услуги = один из специалистов этого направления
        //    (у «Груминг салон» это «Грумер» — связь по колонке activity в field_of_activities);
        //  - старые данные, где название направления записано прямо в specialization_doctor.
        $relevantServices = collect();
        if ($activityName) {
            $relatedSpecialists = FieldOfActivity::specialistNamesForActivity($organization->activityType->activity ?? null);

            $relevantServices = Service::where(function ($q) use ($activityName, $relatedSpecialists) {
                    $q->where('specialization', $activityName)
                      ->orWhere('specialization_doctor', $activityName)
                      ->orWhereIn('specialization_doctor', $relatedSpecialists);
                })
                ->orderBy('name')->get()->unique('name')->values();
        }

        $allServices = Service::orderBy('name')->get()->unique('name')->values();

        // 3. Получаем ВСЕ сущности пользователя (для переключателя в табах)
        $allUserEntities = $this->getAllUserEntities();

        // 4. Передаем всё в шаблон
        return view('pages.owner.organization', compact('organization', 'photos', 'relevantServices', 'allServices', 'allUserEntities'));
    }

    /**
     * Заявка прав на объект ("Это моя организация" / "Это я").
     * Вызывается с публичной карточки организации/клиники/врача/специалиста.
     *
     * 1. Если у пользователя уже есть owner-запись на этот объект — просто
     *    прикрепляет новый документ к ней (повторная заявка после отказа).
     * 2. Если записи нет — создаёт её с is_confirmed = false и сразу
     *    прикрепляет загруженный документ.
     */
    public function claimOwnership(Request $request)
    {
        $request->validate([
            'entity_type'  => 'required|in:clinic,organization,doctor,specialist',
            'entity_id'    => 'required|integer',
            'documents'    => 'required|array|min:1',
            'documents.*'  => 'file|mimes:pdf,jpg,jpeg,png,webp|max:122880',
            'comment'      => 'nullable|string|max:255',
            'personal_data_agreement' => 'required|accepted',
        ], [
            'personal_data_agreement.required' => 'Необходимо согласие на обработку персональных данных.',
            'personal_data_agreement.accepted' => 'Необходимо согласие на обработку персональных данных.',
        ]);

        $userId = Auth::id();
        $type   = $request->entity_type;
        $id     = (int) $request->entity_id;

        // Для специалистов и врачей — один человек не может быть двумя разными людьми.
        // Блокируем если пользователь уже подтверждён как специалист или доктор.
        if (in_array($type, ['specialist', 'doctor'])) {
            $alreadySpecialist = SpecialistOwner::where('user_id', $userId)->where('is_confirmed', true)->exists();
            $alreadyDoctor     = DoctorOwner::where('user_id', $userId)->where('is_confirmed', true)->exists();

            if ($alreadySpecialist || $alreadyDoctor) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Вы уже подтверждены как специалист. Один пользователь не может быть двумя разными специалистами.',
                    ], 403);
                }
                return back()->withErrors(['document' => 'Вы уже подтверждены как специалист.']);
            }

            // Блокируем если есть активная (не отклонённая) заявка на ДРУГОГО специалиста/доктора
            $hasOtherSpecialistClaim = SpecialistOwner::where('user_id', $userId)
                ->where('specialist_id', '!=', $type === 'specialist' ? $id : 0)
                ->where('is_rejected', false)
                ->exists();
            $hasOtherDoctorClaim = DoctorOwner::where('user_id', $userId)
                ->where('doctor_id', '!=', $type === 'doctor' ? $id : 0)
                ->where('is_rejected', false)
                ->exists();

            if ($hasOtherSpecialistClaim || $hasOtherDoctorClaim) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Вы уже подали заявку на подтверждение другого специалиста. Дождитесь проверки или обратитесь в поддержку.',
                    ], 403);
                }
                return back()->withErrors(['document' => 'Заявка на другого специалиста уже существует.']);
            }

            // Если есть отклонённая заявка на ЭТОГО специалиста — проверяем 7 дней
            $ownerModel = $type === 'specialist' ? SpecialistOwner::class : DoctorOwner::class;
            $fkColumn   = $type === 'specialist' ? 'specialist_id' : 'doctor_id';
            $existingRejected = $ownerModel::where('user_id', $userId)
                ->where($fkColumn, $id)
                ->where('is_rejected', true)
                ->first();

            if ($existingRejected) {
                if (!$existingRejected->canReapply()) {
                    $daysLeft = 7 - (int) now()->diffInDays($existingRejected->rejected_at);
                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => "Вы сможете подать повторную заявку через {$daysLeft} дн.",
                        ], 403);
                    }
                    return back()->withErrors(['document' => "Повторная заявка доступна через {$daysLeft} дн."]);
                }
                // 7 дней прошло — удаляем старую запись, даём подать заново
                $existingRejected->documents()->each(fn($d) => $d->delete());
                $existingRejected->delete();
            }
        }

        // Проверяем что объект реально существует
        $entityModel = match ($type) {
            'clinic'       => Clinic::class,
            'organization' => Organization::class,
            'doctor'       => Doctor::class,
            'specialist'   => Specialist::class,
        };
        if (!$entityModel::where('id', $id)->exists()) {
            abort(404, 'Объект не найден');
        }

        $ownerModel = match ($type) {
            'clinic'       => ClinicOwner::class,
            'organization' => OrganizationOwner::class,
            'doctor'       => DoctorOwner::class,
            'specialist'   => SpecialistOwner::class,
        };
        $fkColumn = match ($type) {
            'clinic'       => 'clinic_id',
            'organization' => 'organization_id',
            'doctor'       => 'doctor_id',
            'specialist'   => 'specialist_id',
        };

        // Шаг 1: находим или создаём заявку на владение
        $ownerRow = $ownerModel::firstOrCreate(
            [$fkColumn => $id, 'user_id' => $userId],
            ['is_confirmed' => false]
        );

        // Если заявку уже отклонили ранее — позволяем подать повторно,
        // обнулив старый комментарий администратора
        if (!$ownerRow->is_confirmed && $ownerRow->admin_comment) {
            $ownerRow->update(['admin_comment' => null]);
        }

        // Шаг 2: прикрепляем документы к заявке
        $savedDocuments = [];
        foreach ($request->file('documents') as $file) {
            $path = $file->store('verification-documents/' . $type, 'public');
            $document = $ownerRow->documents()->create([
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'comment'       => $request->comment,
            ]);
            $savedDocuments[] = [
                'id'   => $document->id,
                'url'  => \Illuminate\Support\Facades\Storage::url($path),
                'name' => $document->original_name,
            ];
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'message'   => 'Заявка отправлена на проверку администратору.',
                'documents' => $savedDocuments,
            ]);
        }

        return back()->with('success', 'Заявка отправлена. Мы свяжемся с вами после проверки документов.');
    }

    public function uploadVerificationDocument(Request $request)
    {
    $request->validate([
        'documents'    => 'required|array|min:1',
        'documents.*'  => 'file|mimes:pdf,jpg,jpeg,png,webp|max:122880',
        'entity_type'  => 'required|in:clinic,organization,doctor,specialist',
        'owner_row_id' => 'required|integer',
        'comment'      => 'nullable|string|max:255',
    ]);

    $ownerModel = match ($request->entity_type) {
        'clinic'       => \App\Models\ClinicOwner::class,
        'organization' => \App\Models\OrganizationOwner::class,
        'doctor'       => \App\Models\DoctorOwner::class,
        'specialist'   => \App\Models\SpecialistOwner::class,
    };

    // Проверяем что owner-запись принадлежит текущему пользователю
    $ownerRow = $ownerModel::where('id', $request->owner_row_id)
        ->where('user_id', Auth::id())
        ->firstOrFail();

    $savedDocuments = [];
    foreach ($request->file('documents') as $file) {
        $path = $file->store('verification-documents/' . $request->entity_type, 'public');
        $document = $ownerRow->documents()->create([
            'path'          => $path,
            'original_name' => $file->getClientOriginalName(),
            'comment'       => $request->comment,
        ]);
        $savedDocuments[] = [
            'id'   => $document->id,
            'url'  => \Illuminate\Support\Facades\Storage::url($path),
            'name' => $document->original_name,
        ];
    }

    if ($request->wantsJson()) {
        return response()->json([
            'success'   => true,
            'documents' => $savedDocuments,
        ]);
    }

    return back()->with('success', 'Документ загружен. Ожидайте проверки администратором.');
    }

    /**
     * Отмена заявки на владение (только если ещё не подтверждена).
 */
    public function cancelClaim(string $type, int $id): \Illuminate\Http\JsonResponse
    {
    $userId = Auth::id();

    $ownerModel = match ($type) {
        'clinic'       => ClinicOwner::class,
        'organization' => OrganizationOwner::class,
        'doctor'       => DoctorOwner::class,
        'specialist'   => SpecialistOwner::class,
        default        => abort(404),
    };
    $fkColumn = match ($type) {
        'clinic'       => 'clinic_id',
        'organization' => 'organization_id',
        'doctor'       => 'doctor_id',
        'specialist'   => 'specialist_id',
    };

    $ownerRow = $ownerModel::where('user_id', $userId)
        ->where($fkColumn, $id)
        ->firstOrFail();

    // Нельзя отменить уже подтверждённую заявку
    if ($ownerRow->is_confirmed) {
        return response()->json([
            'success' => false,
            'message' => 'Нельзя отменить уже подтверждённую заявку.',
        ], 403);
    }

    // Удаляем все документы заявки с диска
    foreach ($ownerRow->documents as $doc) {
        $doc->deleteFile();
        $doc->delete();
    }

    // …и переписку с администратором по этой заявке
    $ownerRow->messages()->delete();

    $ownerRow->delete();

    return response()->json(['success' => true]);
    }

    /**
     * Удаление загруженного документа (пока заявка не подтверждена).
 */
    public function deleteVerificationDocument(int $documentId)
    {
    $document = \App\Models\OwnershipDocument::findOrFail($documentId);

    // Проверяем владение через полиморфную связь ownerable -> user_id
    $ownerRow = $document->ownerable;
    if (!$ownerRow || $ownerRow->user_id !== Auth::id()) {
        abort(403, 'Нет прав для удаления этого документа');
    }

    $document->deleteFile();
    $document->delete();

    if (request()->wantsJson()) {
        return response()->json(['success' => true]);
    }

    return back()->with('success', 'Документ удалён');
    }


    public function updateOrganization(Request $request, int $id)
    {
        $this->authorizeOwner('organization', $id);
        $organization = Organization::findOrFail($id);

        $data = $request->validate([
            'name'                 => 'required|string|max:255',
            'field_of_activity_id' => 'nullable|exists:field_of_activities,id',
            'description'          => 'nullable|string',
            'country'              => 'required|string|max:255',
            'region'               => 'nullable|string|max:255',
            'city'                 => 'required|string|max:255',
            'street'               => 'required|string|max:255',
            'house'                => 'nullable|string|max:50',
            'address_comment'      => 'nullable|string|max:500',
            'phone1'               => 'nullable|string|max:30',
            'phone2'               => 'nullable|string|max:30',
            'email'                => 'nullable|email|max:255',
            'website'              => 'nullable|url|max:255',
            'telegram'             => 'nullable|string|max:100',
            'whatsapp'             => 'nullable|string|max:100',
            'max'                  => 'nullable|string|max:100',
            'schedule'             => 'nullable|string|max:255',
            'workdays'             => 'nullable|string|max:255',
            'seo_title'            => 'nullable|string|max:255',
            'seo_description'      => 'nullable|string|max:320',
        ]);

        if ($request->hasFile('logo')) {
            $request->validate(['logo' => 'image|mimes:jpeg,png,jpg,webp|max:2048']);
            if ($organization->logo) Storage::disk('public')->delete($organization->logo);
            $data['logo'] = $request->file('logo')->store('organizations/logos', 'public');
        } elseif ($request->boolean('remove_logo')) {
            if ($organization->logo) Storage::disk('public')->delete($organization->logo);
            $data['logo'] = null;
        }

        if (empty($organization->slug)) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']) . '-' . $id;
        }

        $organization->update($data);

        // Выбрана сфера «Ветеринарная клиника» — карточка переезжает из
        // «Организаций» в «Клиники» (меняется и URL: /organizations/... → /clinics/...).
        if (!empty($data['field_of_activity_id'])) {
            $field = FieldOfActivity::find($data['field_of_activity_id']);

            if ($field && $field->isVetClinic()) {
                $clinic = app(EntityTypeConverter::class)
                    ->organizationToClinic($organization->fresh());

                return redirect()
                    ->route('owner.clinic', ['id' => $clinic->id, 'tab' => 'info'])
                    ->with('success', 'Сфера деятельности изменена: карточка перенесена в раздел «Клиники».');
            }
        }

        return back()->with('success', 'Данные организации обновлены');
    }

    // ══════════════════════════════════════════════════════════
    //  ВРАЧ
    // ══════════════════════════════════════════════════════════

    public function doctor(int $id)
    {
        if ($missing = $this->missingEntityResponse('doctor', $id)) {
            return $missing;
        }

        $this->authorizeOwner('doctor', $id);

        // Если заявка на этот объект ещё не подтверждена — показываем
        // страницу с загрузкой документов и чатом вместо полного кабинета.
        [$ownerRow, $icon] = $this->getOwnerRowFor('doctor', $id);
        if ($ownerRow && !$ownerRow->is_confirmed) {
            $doctor = Doctor::findOrFail($id);
            return view('pages.owner.pending', [
                'ownerRow'        => $ownerRow,
                'entityName'      => $doctor->name,
                'icon'            => $icon,
                'entityId'        => $id,
                'type'            => 'doctor',
                'allUserEntities' => $this->getAllUserEntities(),
            ]);
        }

        $doctor = Doctor::with(['services', 'prices.service', 'contacts', 'city', 'clinic', 'clinics'])->findOrFail($id);
        $photos = EntityPhoto::where('photoable_type', Doctor::class)->where('photoable_id', $id)
                        ->orderBy('sort_order')->get();

        // Услуги, relevant конкретно для специализации этого врача
        // (Doctor.specialization совпадает с Service.specialization_doctor по значению)
        $relevantServices = Service::where('specialization_doctor', $doctor->specialization)
            ->orderBy('name')->get()->unique('name')->values();

        $allServices = Service::whereNotNull('specialization_doctor')
            ->orderBy('name')->get()->unique('name')->values();

        $allUserEntities = $this->getAllUserEntities();

        return view('pages.owner.doctor', compact('doctor', 'photos', 'relevantServices', 'allServices', 'allUserEntities'));
    }

    public function updateDoctor(Request $request, int $id)
    {
        $this->authorizeOwner('doctor', $id);
        $doctor = Doctor::findOrFail($id);

        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'specialization'      => 'required|string|max:255',
            'date_of_birth'       => ['nullable', 'date', 'before_or_equal:' . \App\Models\Doctor::latestBirthDate()],
            'city_id'             => 'required|exists:cities,id',
            // Врач может работать сразу в нескольких клиниках
            'clinic_ids'          => 'nullable|array|max:20',
            'clinic_ids.*'        => 'integer|exists:clinics,id',
            'practice_started_at' => \App\Models\Doctor::practiceStartRules($request->date_of_birth),
            'exotic_animals'      => 'nullable|boolean',
            'On_site_assistance'  => 'nullable|boolean',
            'works_online'        => 'nullable|boolean',
            'description'         => 'nullable|string',
            'seo_title'           => 'nullable|string|max:255',
            'seo_description'     => 'nullable|string|max:320',
            // Контакты (мессенджеры) — сохраняются отдельно в doctor_contacts
            'contact_phone'       => 'nullable|string|max:30',
            'contact_email'       => 'nullable|email|max:255',
            'contact_telegram'    => 'nullable|string|max:100',
            'contact_vk'          => 'nullable|string|max:100',
            'contact_max'         => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('photo')) {
            $request->validate(['photo' => 'image|mimes:jpeg,png,jpg,webp|max:2048']);
            if ($doctor->photo) Storage::disk('public')->delete($doctor->photo);
            $data['photo'] = $request->file('photo')->store('doctors/photos', 'public');
        } elseif ($request->boolean('remove_photo')) {
            if ($doctor->photo) Storage::disk('public')->delete($doctor->photo);
            $data['photo'] = null;
        }

        // Контактные поля не относятся напрямую к таблице doctors — выносим их
        $contactData = [
            'phone'    => $data['contact_phone']    ?? null,
            'email'    => $data['contact_email']    ?? null,
            'telegram' => $data['contact_telegram'] ?? null,
            // На фронте это поле называется "VK", но физически хранится в колонке whatsapp
            'whatsapp' => $data['contact_vk']       ?? null,
            'max'      => $data['contact_max']      ?? null,
        ];
        unset($data['contact_phone'], $data['contact_email'], $data['contact_telegram'], $data['contact_vk'], $data['contact_max']);

        if (empty($doctor->slug)) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']) . '-' . $id;
        }

        $data['works_online'] = $request->boolean('works_online');

        // Места работы сохраняются отдельно (сводная таблица clinic_doctor)
        $clinicIds = $data['clinic_ids'] ?? [];
        unset($data['clinic_ids']);

        $doctor->update($data);
        $doctor->syncWorkplaces($clinicIds);
        $doctor->contacts()->updateOrCreate(['doctor_id' => $doctor->id], $contactData);

        return back()->with('success', 'Данные профиля обновлены');
    }

    // ══════════════════════════════════════════════════════════
    //  СПЕЦИАЛИСТ
    // ══════════════════════════════════════════════════════════

    public function specialist(int $id)
    {
        if ($missing = $this->missingEntityResponse('specialist', $id)) {
            return $missing;
        }

        $this->authorizeOwner('specialist', $id);

        // Если заявка на этот объект ещё не подтверждена — показываем
        // страницу с загрузкой документов и чатом вместо полного кабинета.
        [$ownerRow, $icon] = $this->getOwnerRowFor('specialist', $id);
        if ($ownerRow && !$ownerRow->is_confirmed) {
            $specialist = Specialist::findOrFail($id);
            return view('pages.owner.pending', [
                'ownerRow'        => $ownerRow,
                'entityName'      => $specialist->name,
                'icon'            => $icon,
                'entityId'        => $id,
                'type'            => 'specialist',
                'allUserEntities' => $this->getAllUserEntities(),
            ]);
        }

        $specialist = Specialist::with(['prices.service', 'contacts', 'city', 'organization', 'organizations'])->findOrFail($id);
        $photos     = EntityPhoto::where('photoable_type', Specialist::class)->where('photoable_id', $id)
                        ->orderBy('sort_order')->get();

        // Услуги, relevant конкретно для специализации этого специалиста
        // (Specialist.specialization совпадает с Service.specialization_doctor по значению)
        $relevantServices = Service::where('specialization_doctor', $specialist->specialization)
            ->orderBy('name')->get()->unique('name')->values();

        $allServices = Service::orderBy('name')->get()->unique('name')->values();

        $allUserEntities = $this->getAllUserEntities();

        return view('pages.owner.specialist', compact('specialist', 'photos', 'relevantServices', 'allServices', 'allUserEntities'));
    }

    public function updateSpecialist(Request $request, int $id)
    {
        $this->authorizeOwner('specialist', $id);
        $specialist = Specialist::findOrFail($id);

        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'specialization'      => 'required|string|max:255',
            'date_of_birth'       => ['nullable', 'date', 'before_or_equal:' . \App\Models\Specialist::latestBirthDate()],
            'city_id'             => 'required|exists:cities,id',
            // Специалист может работать сразу в нескольких организациях
            'organization_ids'    => 'nullable|array|max:20',
            'organization_ids.*'  => 'integer|exists:organizations,id',
            'practice_started_at' => \App\Models\Specialist::practiceStartRules($request->date_of_birth),
            'exotic_animals'      => 'nullable|boolean',
            'On_site_assistance'  => 'nullable|boolean',
            'works_online'        => 'nullable|boolean',
            'description'         => 'nullable|string',
            'seo_title'           => 'nullable|string|max:255',
            'seo_description'     => 'nullable|string|max:320',
            // Контакты (мессенджеры) — сохраняются отдельно в specialist_contacts
            'contact_phone'       => 'nullable|string|max:30',
            'contact_email'       => 'nullable|email|max:255',
            'contact_telegram'    => 'nullable|string|max:100',
            'contact_vk'          => 'nullable|string|max:100',
            'contact_max'         => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('photo')) {
            $request->validate(['photo' => 'image|mimes:jpeg,png,jpg,webp|max:2048']);
            if ($specialist->photo) Storage::disk('public')->delete($specialist->photo);
            $data['photo'] = $request->file('photo')->store('specialists/photos', 'public');
        } elseif ($request->boolean('remove_photo')) {
            if ($specialist->photo) Storage::disk('public')->delete($specialist->photo);
            $data['photo'] = null;
        }

        // specialist_contacts: telegram/whatsapp/max — старые boolean-колонки не трогаем,
        // используем новые текстовые *_text поля, добавленные отдельной миграцией
        $contactData = [
            'phone'         => $data['contact_phone']    ?? null,
            'email'         => $data['contact_email']    ?? null,
            'telegram_text' => $data['contact_telegram'] ?? null,
            // На фронте это поле называется "VK", физически — whatsapp_text
            'whatsapp_text' => $data['contact_vk']       ?? null,
            'max_text'      => $data['contact_max']      ?? null,
        ];
        unset($data['contact_phone'], $data['contact_email'], $data['contact_telegram'], $data['contact_vk'], $data['contact_max']);

        $data['works_online'] = $request->boolean('works_online');

        // Места работы сохраняются отдельно (сводная таблица organization_specialist)
        $organizationIds = $data['organization_ids'] ?? [];
        unset($data['organization_ids']);

        $specialist->update($data);
        $specialist->syncWorkplaces($organizationIds);
        $specialist->contacts()->updateOrCreate(['specialist_id' => $specialist->id], $contactData);

        return back()->with('success', 'Данные профиля обновлены');
    }

    // ══════════════════════════════════════════════════════════
    //  ФОТОГРАФИИ (общий для всех типов)
    // ══════════════════════════════════════════════════════════

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo'          => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
            'entity_type'    => 'required|in:clinic,organization,doctor,specialist',
            'entity_id'      => 'required|integer',
            'caption'        => 'nullable|string|max:255',
        ]);

        $this->authorizeOwner($request->entity_type, $request->entity_id);

        $morphMap = [
            'clinic'       => Clinic::class,
            'organization' => Organization::class,
            'doctor'       => Doctor::class,
            'specialist'   => Specialist::class,
        ];

        $entityClass = $morphMap[$request->entity_type];
        $entity = $entityClass::findOrFail($request->entity_id);

        $limit = $entity->galleryPhotoLimitForOwner();
        $current = $entity->photos()->count();

        if ($current >= $limit) {
            $message = $limit === 1
                ? 'Бесплатно доступна только 1 фотография. Чтобы загружать больше (до 15), подключите рекламный пакет.'
                : 'Достигнут лимит фотографий (' . $limit . ').';

            return response()->json(['success' => false, 'message' => $message], 422);
        }

        $path = $request->file('photo')->store(
            $request->entity_type . 's/gallery',
            'public'
        );

        $maxOrder = EntityPhoto::where('photoable_type', $morphMap[$request->entity_type])
            ->where('photoable_id', $request->entity_id)
            ->max('sort_order') ?? 0;

        $photo = EntityPhoto::create([
            'photoable_type' => $morphMap[$request->entity_type],
            'photoable_id'   => $request->entity_id,
            'path'           => $path,
            'caption'        => $request->caption,
            'sort_order'     => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'photo'   => [
                'id'      => $photo->id,
                'url'     => Storage::url($path),
                'caption' => $photo->caption,
            ],
        ]);
    }

    /**
     * Смена порядка фотографий (drag-and-drop в личном кабинете).
     */
    public function reorderPhotos(Request $request)
    {
        $data = $request->validate([
            'entity_type' => 'required|in:clinic,organization,doctor,specialist',
            'entity_id'   => 'required|integer',
            'order'       => 'required|array',
            'order.*'     => 'integer',
        ]);

        $this->authorizeOwner($data['entity_type'], $data['entity_id']);

        $morphMap = [
            'clinic'       => Clinic::class,
            'organization' => Organization::class,
            'doctor'       => Doctor::class,
            'specialist'   => Specialist::class,
        ];

        $ownedIds = EntityPhoto::where('photoable_type', $morphMap[$data['entity_type']])
            ->where('photoable_id', $data['entity_id'])
            ->pluck('id')
            ->all();

        foreach ($data['order'] as $index => $photoId) {
            if (!in_array((int) $photoId, $ownedIds, true)) {
                continue;
            }
            EntityPhoto::where('id', $photoId)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function deletePhoto(int $photoId)
    {
        $photo = EntityPhoto::findOrFail($photoId);

        // Определяем тип и id сущности из morph
        $typeMap = [
            Clinic::class       => 'clinic',
            Organization::class => 'organization',
            Doctor::class       => 'doctor',
            Specialist::class   => 'specialist',
        ];

        $entityType = $typeMap[$photo->photoable_type] ?? null;
        if ($entityType) {
            $this->authorizeOwner($entityType, $photo->photoable_id);
        }

        $photo->deleteFile();
        $photo->delete();

        return response()->json(['success' => true]);
    }

    // ══════════════════════════════════════════════════════════
    //  ЦЕНЫ / УСЛУГИ (общий для всех)
    // ══════════════════════════════════════════════════════════

    // ══════════════════════════════════════════════════════════
    //  ЦЕНЫ / УСЛУГИ
    // ══════════════════════════════════════════════════════════

    public function savePrice(Request $request)
    {
        $request->validate([
            'entity_type'      => 'required|in:clinic,organization,doctor,specialist',
            'entity_id'        => 'required|integer',
            'service_id'       => 'required_without:new_service_name|nullable|exists:services,id',
            'new_service_name' => 'required_without:service_id|nullable|string|max:255',
            'price'            => 'required|numeric|min:0',
            'currency'         => 'nullable|string|max:10',
        ]);

        $this->authorizeOwner($request->entity_type, $request->entity_id);

        // Владелец может не найти нужную услугу в каталоге и ввести своё название —
        // тогда создаём (или переиспользуем, если такая уже есть) услугу на лету.
        if ($request->filled('new_service_name')) {
            $service = Service::firstOrCreate([
                'name' => trim($request->new_service_name),
            ]);
            $serviceId = $service->id;
        } else {
            $serviceId = $request->service_id;
        }

        $morphMap = [
            'clinic'       => Clinic::class,
            'organization' => Organization::class,
            'doctor'       => Doctor::class,
            'specialist'   => Specialist::class,
        ];

        Price::updateOrCreate(
            [
                'priceable_type' => $morphMap[$request->entity_type],
                'priceable_id'   => $request->entity_id,
                'service_id'     => $serviceId,
            ],
            [
                'price'    => $request->price,
                'currency' => $request->currency ?? 'руб.',
            ]
        );

        return response()->json(['success' => true]);
    }

    public function deletePrice(int $priceId)
    {
        $price = Price::findOrFail($priceId);
        $price->delete();
        return response()->json(['success' => true]);
    }

        // ══════════════════════════════════════════════════════════
    //  АКЦИИ (PROMOTIONS)
    // ══════════════════════════════════════════════════════════

    public function savePromotion(Request $request)
    {
        $request->validate(['entity_type'=>'required|in:clinic,organization,doctor,specialist','entity_id'=>'required|integer','title'=>'required|string|max:100','description'=>'nullable|string|max:500','old_price'=>'nullable|numeric|min:0','new_price'=>'nullable|numeric|min:0','badge'=>'nullable|string|max:20','expires_at'=>'nullable|date|after_or_equal:today']);
        $this->authorizeOwner($request->entity_type, $request->entity_id);
        if (!Auth::user()->hasPromoPackage()) return response()->json(['success'=>false,'message'=>'Рекламный пакет не активен.'],403);
        $morphMap=['clinic'=>\App\Models\Clinic::class,'organization'=>\App\Models\Organization::class,'doctor'=>\App\Models\Doctor::class,'specialist'=>\App\Models\Specialist::class];
        if (\App\Models\Promotion::where('promotable_type',$morphMap[$request->entity_type])->where('promotable_id',$request->entity_id)->count()>=3) return response()->json(['success'=>false,'message'=>'Максимум 3 акции.'],422);
        \App\Models\Promotion::create(['promotable_type'=>$morphMap[$request->entity_type],'promotable_id'=>$request->entity_id,'title'=>$request->title,'description'=>$request->description,'old_price'=>$request->old_price,'new_price'=>$request->new_price,'badge'=>$request->badge,'expires_at'=>$request->expires_at,'is_active'=>true]);
        return response()->json(['success'=>true]);
    }

    public function deletePromotion(int $promotionId)
    {
        $promo = \App\Models\Promotion::findOrFail($promotionId);
        $map=[\App\Models\Clinic::class=>'clinic',\App\Models\Organization::class=>'organization',\App\Models\Doctor::class=>'doctor',\App\Models\Specialist::class=>'specialist'];
        $type=$map[$promo->promotable_type]??null;
        if($type)$this->authorizeOwner($type,$promo->promotable_id);
        $promo->delete();
        return response()->json(['success'=>true]);
    }

    // ══ ЧАТ ══

    public function sendClaimMessage(Request $request)
    {
        $request->validate(['entity_type'=>'required|in:clinic,organization,doctor,specialist','owner_row_id'=>'required|integer','message'=>'required|string|max:2000']);
        $ownerModel=match($request->entity_type){'clinic'=>\App\Models\ClinicOwner::class,'organization'=>\App\Models\OrganizationOwner::class,'doctor'=>\App\Models\DoctorOwner::class,'specialist'=>\App\Models\SpecialistOwner::class};
        $ownerRow=$ownerModel::where('id',$request->owner_row_id)->where('user_id',Auth::id())->firstOrFail();
        $msg=\App\Models\OwnerClaimMessage::create(['claimable_type'=>$ownerModel,'claimable_id'=>$ownerRow->id,'user_id'=>Auth::id(),'is_admin'=>false,'message'=>$request->message,'is_read'=>false]);
        return response()->json(['success'=>true,'message'=>['id'=>$msg->id,'text'=>$msg->message,'is_admin'=>false,'author'=>Auth::user()->name,'created_at'=>$msg->created_at->format('d.m.Y H:i')]]);
    }

    public function getClaimMessages(Request $request)
    {
        $request->validate(['entity_type'=>'required|in:clinic,organization,doctor,specialist','owner_row_id'=>'required|integer']);
        $ownerModel=match($request->entity_type){'clinic'=>\App\Models\ClinicOwner::class,'organization'=>\App\Models\OrganizationOwner::class,'doctor'=>\App\Models\DoctorOwner::class,'specialist'=>\App\Models\SpecialistOwner::class};
        $ownerRow=$ownerModel::where('id',$request->owner_row_id)->where('user_id',Auth::id())->firstOrFail();
        $messages=$ownerRow->messages()->with('user')->get();
        $ownerRow->messages()->where('is_admin',true)->where('is_read',false)->update(['is_read'=>true]);
        return response()->json(['success'=>true,'messages'=>$messages->map(fn($m)=>['id'=>$m->id,'text'=>$m->message,'is_admin'=>$m->is_admin,'author'=>$m->is_admin?'Администратор':($m->user->name??'Вы'),'created_at'=>$m->created_at->format('d.m.Y H:i')])]);
    }

    // ══════════════════════════════════════════════════════════
    //  УДАЛЕНИЕ КАРТОЧКИ ВЛАДЕЛЬЦЕМ
    // ══════════════════════════════════════════════════════════

    /**
     * Удаляет карточку (клиника / организация / врач / специалист) по просьбе владельца.
     * Перед удалением сохраняет копию данных и причину в «Обратную связь» (админка).
     */
    public function deleteCard(Request $request, string $type, int $id)
    {
        $this->authorizeOwner($type, $id);

        [$ownerRow] = $this->getOwnerRowFor($type, $id);
        if (!$ownerRow || !$ownerRow->is_confirmed) {
            abort(403, 'Удалять карточку может только подтверждённый владелец.');
        }

        $request->validate([
            'reason'         => ['required', 'string', 'min:5', 'max:2000'],
            'confirm_delete' => ['accepted'],
        ], [
            'reason.required'        => 'Укажите, пожалуйста, почему вы удаляете карточку.',
            'reason.min'             => 'Опишите причину подробнее (минимум 5 символов).',
            'confirm_delete.accepted' => 'Поставьте галочку «Я хочу удалить карточку».',
        ]);

        [, , , $entityModel] = $this->ownerMap()[$type];
        $entity = $entityModel::find($id);

        if (!$entity) {
            return redirect()->route('account');
        }

        $user = Auth::user();

        // ── Копия данных карточки для админки ──
        $region = $entity->region ?? null;
        $city   = $entity->city ?? null;
        $activity = null;
        $address  = null;

        if (in_array($type, ['organization', 'clinic'])) {
            $activity = $type === 'organization'
                ? ($entity->activityType->name ?? null)
                : 'Клиника';
            $address = implode(', ', array_filter([$entity->street ?? null, $entity->house ?? null]));
        } else {
            // у врачей и специалистов город — связь с таблицей cities
            $cityModel = $entity->city_id ? \App\Models\City::find($entity->city_id) : null;
            $region   = $cityModel->region ?? null;
            $city     = $cityModel->name ?? null;
            $activity = $entity->specialization ?? null;
        }

        DB::transaction(function () use ($request, $type, $id, $entity, $user, $region, $city, $activity, $address) {
            OwnerFeedback::create([
                'user_id'       => $user->id,
                'user_name'     => $user->name ?? $user->email,
                'entity_type'   => $type,
                'entity_id'     => $id,
                'entity_name'   => $entity->name ?? null,
                'activity_type' => $activity,
                'region'        => $region,
                'city'          => $city,
                'address'       => $address ?: null,
                'snapshot'      => $entity->attributesToArray(),
                'reason'        => trim($request->input('reason')),
                'is_read'       => false,
            ]);

            // Фото галереи + файлы
            foreach ($entity->photos ?? [] as $photo) {
                if ($photo->path) Storage::disk('public')->delete($photo->path);
                $photo->delete();
            }
            foreach (['logo', 'photo'] as $field) {
                if (!empty($entity->{$field})) Storage::disk('public')->delete($entity->{$field});
            }

            // Цены и акции
            if (method_exists($entity, 'prices'))     $entity->prices()->delete();
            if (method_exists($entity, 'promotions')) $entity->promotions()->delete();

            // Записи владения (у всех пользователей) вместе с документами и перепиской
            [$ownerModel, $fk] = $this->ownerMap()[$type];
            $ownerModel::where($fk, $id)->get()->each(function ($row) {
                $row->documents()->delete();
                $row->messages()->delete();
                $row->delete();
            });

            // Специалисты/врачи, привязанные к этому месту работы, остаются: при удалении
            // клиники/организации она убирается из их мест работы (см. события deleting в моделях).
            $entity->delete();
        });

        return redirect()->route('account')->with('success', 'Карточка удалена. Спасибо, что сообщили причину.');
    }

private function authorizeOwner(string $type, int $entityId): void
    {
        $userId = Auth::id();

        // Проверяем, привязан ли в принципе этот объект к пользователю (без жесткого условия на true)
        $exists = match($type) {
            'clinic'       => ClinicOwner::where('user_id', $userId)->where('clinic_id', $entityId)->exists(),
            'organization' => OrganizationOwner::where('user_id', $userId)->where('organization_id', $entityId)->exists(),
            'doctor'       => DoctorOwner::where('user_id', $userId)->where('doctor_id', $entityId)->exists(),
            'specialist'   => SpecialistOwner::where('user_id', $userId)->where('specialist_id', $entityId)->exists(),
            default        => false,
        };

        if (!$exists) {
            abort(403, 'У вас нет прав для управления этим объектом.');
        }
    }
}