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
use App\Models\EntityPhoto;
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
    private function getAllUserEntities()
    {
        $user = Auth::user();
        $entities = collect();

        foreach (ClinicOwner::where('user_id', $user->id)->get() as $row) {
            $entities->push([
                'id' => $row->clinic_id, 'type' => 'clinic', 'name' => $row->clinic?->name ?? 'Клиника', 'is_confirmed' => $row->is_confirmed, 'icon' => '🏥'
            ]);
        }
        foreach (OrganizationOwner::where('user_id', $user->id)->get() as $row) {
            $entities->push([
                'id' => $row->organization_id, 'type' => 'organization', 'name' => $row->organization?->name ?? 'Организация', 'is_confirmed' => $row->is_confirmed, 'icon' => '🏢'
            ]);
        }
        foreach (DoctorOwner::where('user_id', $user->id)->get() as $row) {
            $entities->push([
                'id' => $row->doctor_id, 'type' => 'doctor', 'name' => $row->doctor?->name ?? 'Врач', 'is_confirmed' => $row->is_confirmed, 'icon' => '👨‍⚕️'
            ]);
        }
        foreach (SpecialistOwner::where('user_id', $user->id)->get() as $row) {
            $entities->push([
                'id' => $row->specialist_id, 'type' => 'specialist', 'name' => $row->specialist?->name ?? 'Специалист', 'is_confirmed' => $row->is_confirmed, 'icon' => '🩺'
            ]);
        }

        return $entities;
    }

    /**
     * Получить только сущности на модерации
     */
    private function getPendingOwners()
    {
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


    // ══════════════════════════════════════════════════════════
    //  КЛИНИКА
    // ══════════════════════════════════════════════════════════

    public function clinic(int $id)
    {
        $this->authorizeOwner('clinic', $id);

        $clinic = Clinic::with(['services', 'prices.service', 'doctors', 'awards'])->findOrFail($id);
        $photos = EntityPhoto::where('photoable_type', Clinic::class)->where('photoable_id', $id)
                        ->orderBy('sort_order')->get();

        // Клиника — это весь спектр ветеринарных услуг (все специализации врачей)
        $doctorActivityNames = FieldOfActivity::where('type', 'specialist')
            ->where('activity', 'doctor')
            ->pluck('name');

        $relevantServices = Service::whereIn('specialization_doctor', $doctorActivityNames)
            ->orderBy('name')->get()->unique('name')->values();

        $allServices = Service::orderBy('name')->get()->unique('name')->values();

        return view('pages.owner.clinic', compact('clinic', 'photos', 'relevantServices', 'allServices', 'service',));
    }

    public function updateClinic(Request $request, int $id)
    {
        $this->authorizeOwner('clinic', $id);
        $clinic = Clinic::findOrFail($id);

        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'slug'            => 'required|string|max:255|alpha_dash|unique:clinics,slug,' . $id,
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
        ]);

        if ($request->hasFile('logo')) {
            $request->validate(['logo' => 'image|mimes:jpeg,png,jpg,webp|max:2048']);
            if ($clinic->logo) Storage::disk('public')->delete($clinic->logo);
            $data['logo'] = $request->file('logo')->store('clinics/logos', 'public');
        }

        $clinic->update($data);

        return back()->with('success', 'Данные клиники обновлены');
    }

    // ══════════════════════════════════════════════════════════
    //  ОРГАНИЗАЦИЯ
    // ══════════════════════════════════════════════════════════

public function organization(int $id)
    {
        // 1. Проверяем права (доступно только если организация подтверждена)
        $this->authorizeOwner('organization', $id);

        // 2. Выбираем данные организации, фото и список услуг
        $organization = Organization::with(['prices.service', 'activityType'])->findOrFail($id);
        $photos = EntityPhoto::where('photoable_type', Organization::class)
            ->where('photoable_id', $id)
            ->orderBy('sort_order')
            ->get();

        // Услуги, relevant конкретно для сферы деятельности этой организации
        // (FieldOfActivity.name совпадает с Service.specialization_doctor по значению)
        $activityName = $organization->activityType->name ?? null;

        $relevantServices = $activityName
            ? Service::where('specialization_doctor', $activityName)->orderBy('name')->get()->unique('name')->values()
            : collect();

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
            'slug'                 => 'required|string|max:255|alpha_dash|unique:organizations,slug,' . $id,
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
        }

        $organization->update($data);

        return back()->with('success', 'Данные организации обновлены');
    }

    // ══════════════════════════════════════════════════════════
    //  ВРАЧ
    // ══════════════════════════════════════════════════════════

    public function doctor(int $id)
    {
        $this->authorizeOwner('doctor', $id);

        $doctor = Doctor::with(['services', 'prices.service', 'contacts'])->findOrFail($id);
        $photos = EntityPhoto::where('photoable_type', Doctor::class)->where('photoable_id', $id)
                        ->orderBy('sort_order')->get();

        // Услуги, relevant конкретно для специализации этого врача
        // (Doctor.specialization совпадает с Service.specialization_doctor по значению)
        $relevantServices = Service::where('specialization_doctor', $doctor->specialization)
            ->orderBy('name')->get()->unique('name')->values();

        $allServices = Service::whereNotNull('specialization_doctor')
            ->orderBy('name')->get()->unique('name')->values();

        return view('pages.owner.doctor', compact('doctor', 'photos', 'service', 'relevantServices', 'allServices'));
    }

    public function updateDoctor(Request $request, int $id)
    {
        $this->authorizeOwner('doctor', $id);
        $doctor = Doctor::findOrFail($id);

        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'slug'                => 'required|string|max:255|alpha_dash|unique:doctors,slug,' . $id,
            'specialization'      => 'required|string|max:255',
            'date_of_birth'       => 'nullable|date',
            'city_id'             => 'required|exists:cities,id',
            'clinic_id'           => 'nullable|exists:clinics,id',
            'experience'          => 'nullable|integer|min:0|max:70',
            'exotic_animals'      => 'nullable|boolean',
            'On_site_assistance'  => 'nullable|boolean',
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

        $doctor->update($data);
        $doctor->contacts()->updateOrCreate(['doctor_id' => $doctor->id], $contactData);

        return back()->with('success', 'Данные профиля обновлены');
    }

    // ══════════════════════════════════════════════════════════
    //  СПЕЦИАЛИСТ
    // ══════════════════════════════════════════════════════════

    public function specialist(int $id)
    {
        $this->authorizeOwner('specialist', $id);

        $specialist = Specialist::with(['prices.service', 'contacts'])->findOrFail($id);
        $photos     = EntityPhoto::where('photoable_type', Specialist::class)->where('photoable_id', $id)
                        ->orderBy('sort_order')->get();

        // Услуги, relevant конкретно для специализации этого специалиста
        // (Specialist.specialization совпадает с Service.specialization_doctor по значению)
        $relevantServices = Service::where('specialization_doctor', $specialist->specialization)
            ->orderBy('name')->get()->unique('name')->values();

        $allServices = Service::orderBy('name')->get()->unique('name')->values();

        return view('pages.owner.specialist', compact('specialist', 'photos', 'service', 'relevantServices', 'allServices'));
    }

    public function updateSpecialist(Request $request, int $id)
    {
        $this->authorizeOwner('specialist', $id);
        $specialist = Specialist::findOrFail($id);

        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'specialization'      => 'required|string|max:255',
            'date_of_birth'       => 'nullable|date',
            'city_id'             => 'required|exists:cities,id',
            'organization_id'     => 'nullable|exists:organizations,id',
            'experience'          => 'nullable|integer|min:0|max:70',
            'exotic_animals'      => 'nullable|boolean',
            'On_site_assistance'  => 'nullable|boolean',
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

        $specialist->update($data);
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

    public func
    // ══════════════════════════════════════════════════════════
    //  АКЦИИ (PROMOTIONS)
    // ══════════════════════════════════════════════════════════

    public function savePromotion(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|in:clinic,organization,doctor,specialist',
            'entity_id'   => 'required|integer',
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'old_price'   => 'nullable|numeric|min:0',
            'new_price'   => 'nullable|numeric|min:0',
            'badge'       => 'nullable|string|max:20',
            'expires_at'  => 'nullable|date|after_or_equal:today',
        ]);

        $this->authorizeOwner($request->entity_type, $request->entity_id);

        if (!Auth::user()->hasPromoPackage()) {
            return response()->json(['success' => false, 'message' => 'Рекламный пакет не активен.'], 403);
        }

        $morphMap = [
            'clinic'       => \App\Models\Clinic::class,
            'organization' => \App\Models\Organization::class,
            'doctor'       => \App\Models\Doctor::class,
            'specialist'   => \App\Models\Specialist::class,
        ];

        $count = \App\Models\Promotion::where('promotable_type', $morphMap[$request->entity_type])
            ->where('promotable_id', $request->entity_id)
            ->count();

        if ($count >= 3) {
            return response()->json(['success' => false, 'message' => 'Максимум 3 акции на одну карточку.'], 422);
        }

        \App\Models\Promotion::create([
            'promotable_type' => $morphMap[$request->entity_type],
            'promotable_id'   => $request->entity_id,
            'title'           => $request->title,
            'description'     => $request->description,
            'old_price'       => $request->old_price,
            'new_price'       => $request->new_price,
            'badge'           => $request->badge,
            'expires_at'      => $request->expires_at,
            'is_active'       => true,
        ]);

        return response()->json(['success' => true]);
    }

    public function deletePromotion(int $promotionId)
    {
        $promo = \App\Models\Promotion::findOrFail($promotionId);

        $morphToType = [
            \App\Models\Clinic::class       => 'clinic',
            \App\Models\Organization::class => 'organization',
            \App\Models\Doctor::class       => 'doctor',
            \App\Models\Specialist::class   => 'specialist',
        ];

        $type = $morphToType[$promo->promotable_type] ?? null;
        if ($type) {
            $this->authorizeOwner($type, $promo->promotable_id);
        }

        $promo->delete();
        return response()->json(['success' => true]);
    }

    tion savePrice(Request $request)
    {
        $request->validate([
            'entity_type' => 'required|in:clinic,organization,doctor,specialist',
            'entity_id'   => 'required|integer',
            'service_id'  => 'required|exists:services,id',
            'price'       => 'required|numeric|min:0',
            'currency'    => 'nullable|string|max:10',
        ]);

        $this->authorizeOwner($request->entity_type, $request->entity_id);

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
                'service_id'     => $request->service_id,
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
    //  ПРОВЕРКА ПРАВ
    // ══════════════════════════════════════════════════════════



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