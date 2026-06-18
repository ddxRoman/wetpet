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
public function index()
{
    $user = Auth::user();

    // Сначала проверяем подтверждённые — редирект сразу в кабинет
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

    // Нет подтверждённых — собираем ВСЕ pending-заявки с документами
    $pendingOwners = collect();

    $clinicOwners = ClinicOwner::with(['clinic', 'documents'])
        ->where('user_id', $user->id)
        ->where('is_confirmed', false)
        ->get()
        ->map(fn($o) => [
            'owner_row'   => $o,
            'entity_type' => 'clinic',
            'entity_name' => $o->clinic?->name ?? 'Клиника',
            'icon'        => '🏥',
        ]);

    $orgOwners = OrganizationOwner::with(['organization', 'documents'])
        ->where('user_id', $user->id)
        ->where('is_confirmed', false)
        ->get()
        ->map(fn($o) => [
            'owner_row'   => $o,
            'entity_type' => 'organization',
            'entity_name' => $o->organization?->name ?? 'Организация',
            'icon'        => '🏢',
        ]);

    $doctorOwners = DoctorOwner::with(['doctor', 'documents'])
        ->where('user_id', $user->id)
        ->where('is_confirmed', false)
        ->get()
        ->map(fn($o) => [
            'owner_row'   => $o,
            'entity_type' => 'doctor',
            'entity_name' => $o->doctor?->name ?? 'Врач',
            'icon'        => '👨‍⚕️',
        ]);

    $specOwners = SpecialistOwner::with(['specialist', 'documents'])
        ->where('user_id', $user->id)
        ->where('is_confirmed', false)
        ->get()
        ->map(fn($o) => [
            'owner_row'   => $o,
            'entity_type' => 'specialist',
            'entity_name' => $o->specialist?->name ?? 'Специалист',
            'icon'        => '🩺',
        ]);

    $pendingOwners = $clinicOwners
        ->concat($orgOwners)
        ->concat($doctorOwners)
        ->concat($specOwners);

    return view('pages.owner.no-access', compact('pendingOwners'));
}


    // ══════════════════════════════════════════════════════════
    //  КЛИНИКА
    // ══════════════════════════════════════════════════════════

    public function clinic(int $id)
    {
        $this->authorizeOwner('clinic', $id);

        $clinic   = Clinic::with(['services', 'prices.service', 'doctors', 'awards'])->findOrFail($id);
        $photos   = EntityPhoto::where('photoable_type', Clinic::class)->where('photoable_id', $id)
                        ->orderBy('sort_order')->get();
        $services = Service::orderBy('name')->get();

        return view('pages.owner.clinic', compact('clinic', 'photos', 'services'));
    }

    public function updateClinic(Request $request, int $id)
    {
        $this->authorizeOwner('clinic', $id);
        $clinic = Clinic::findOrFail($id);

        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'phone1'          => 'nullable|string|max:30',
            'phone2'          => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:255',
            'website'         => 'nullable|url|max:255',
            'telegram'        => 'nullable|string|max:100',
            'whatsapp'        => 'nullable|string|max:30',
            'schedule'        => 'nullable|string',
            'address_comment' => 'nullable|string|max:500',
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
        $this->authorizeOwner('organization', $id);

        $organization = Organization::with(['prices.service', 'activityType'])->findOrFail($id);
        $photos = EntityPhoto::where('photoable_type', Organization::class)->where('photoable_id', $id)
                    ->orderBy('sort_order')->get();
        $services = Service::orderBy('name')->get();

        return view('pages.owner.organization', compact('organization', 'photos', 'services'));
    }

    public function uploadVerificationDocument(Request $request)
{
    $request->validate([
        'document'    => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:8192',
        'entity_type' => 'required|in:clinic,organization,doctor,specialist',
        'owner_row_id'=> 'required|integer',
        'comment'     => 'nullable|string|max:255',
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

    $file = $request->file('document');
    $path = $file->store('verification-documents/' . $request->entity_type, 'public');

    $document = $ownerRow->documents()->create([
        'path'          => $path,
        'original_name' => $file->getClientOriginalName(),
        'comment'       => $request->comment,
    ]);

    if ($request->wantsJson()) {
        return response()->json([
            'success'  => true,
            'document' => [
                'id'   => $document->id,
                'url'  => \Illuminate\Support\Facades\Storage::url($path),
                'name' => $document->original_name,
            ],
        ]);
    }

    return back()->with('success', 'Документ загружен. Ожидайте проверки администратором.');
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
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'phone1'          => 'nullable|string|max:30',
            'phone2'          => 'nullable|string|max:30',
            'email'           => 'nullable|email|max:255',
            'website'         => 'nullable|url|max:255',
            'telegram'        => 'nullable|string|max:100',
            'whatsapp'        => 'nullable|string|max:30',
            'schedule'        => 'nullable|string',
            'address_comment' => 'nullable|string|max:500',
            'seo_title'       => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:320',
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

        $doctor   = Doctor::with(['services', 'prices.service', 'contacts'])->findOrFail($id);
        $photos   = EntityPhoto::where('photoable_type', Doctor::class)->where('photoable_id', $id)
                        ->orderBy('sort_order')->get();
        $services = Service::where('specialization_doctor', '!=', null)->orderBy('name')->get();

        return view('pages.owner.doctor', compact('doctor', 'photos', 'services'));
    }

    public function updateDoctor(Request $request, int $id)
    {
        $this->authorizeOwner('doctor', $id);
        $doctor = Doctor::findOrFail($id);

        $data = $request->validate([
            'description'     => 'nullable|string',
            'experience'      => 'nullable|integer|min:0|max:70',
            'exotic_animals'  => 'nullable|boolean',
            'On_site_assistance' => 'nullable|boolean',
            'seo_title'       => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:320',
        ]);

        if ($request->hasFile('photo')) {
            $request->validate(['photo' => 'image|mimes:jpeg,png,jpg,webp|max:2048']);
            if ($doctor->photo) Storage::disk('public')->delete($doctor->photo);
            $data['photo'] = $request->file('photo')->store('doctors/photos', 'public');
        }

        $doctor->update($data);

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
        $services   = Service::orderBy('name')->get();

        return view('pages.owner.specialist', compact('specialist', 'photos', 'services'));
    }

    public function updateSpecialist(Request $request, int $id)
    {
        $this->authorizeOwner('specialist', $id);
        $specialist = Specialist::findOrFail($id);

        $data = $request->validate([
            'description'        => 'nullable|string',
            'experience'         => 'nullable|integer|min:0|max:70',
            'exotic_animals'     => 'nullable|boolean',
            'On_site_assistance' => 'nullable|boolean',
            'seo_title'          => 'nullable|string|max:255',
            'seo_description'    => 'nullable|string|max:320',
        ]);

        if ($request->hasFile('photo')) {
            $request->validate(['photo' => 'image|mimes:jpeg,png,jpg,webp|max:2048']);
            if ($specialist->photo) Storage::disk('public')->delete($specialist->photo);
            $data['photo'] = $request->file('photo')->store('specialists/photos', 'public');
        }

        $specialist->update($data);

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

    public function savePrice(Request $request)
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

        $confirmed = match($type) {
            'clinic'       => ClinicOwner::where('user_id', $userId)->where('clinic_id', $entityId)->where('is_confirmed', true)->exists(),
            'organization' => OrganizationOwner::where('user_id', $userId)->where('organization_id', $entityId)->where('is_confirmed', true)->exists(),
            'doctor'       => DoctorOwner::where('user_id', $userId)->where('doctor_id', $entityId)->where('is_confirmed', true)->exists(),
            'specialist'   => SpecialistOwner::where('user_id', $userId)->where('specialist_id', $entityId)->where('is_confirmed', true)->exists(),
            default        => false,
        };

        if (!$confirmed) {
            abort(403, 'Доступ запрещён — право не подтверждено');
        }
    }
}
