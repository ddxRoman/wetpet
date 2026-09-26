<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\ClinicOwner;
use App\Models\Doctor;
use App\Models\DoctorOwner;
use App\Models\Organization;
use App\Models\OrganizationOwner;
use App\Models\Photo;
use App\Models\Specialist;
use App\Models\SpecialistOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Управление фотогалереей карточек (организации, клиники, врачи, специалисты)
 * из личного кабинета владельца. Загрузка через админку (Filament) сделана
 * отдельными RelationManager'ами и через этот контроллер не идёт.
 */
class GalleryController extends Controller
{
    /**
     * Соответствие типа из URL модели и таблице верификации владельца.
     */
    private const TYPES = [
        'organization' => [Organization::class, OrganizationOwner::class, 'organization_id'],
        'clinic'       => [Clinic::class, ClinicOwner::class, 'clinic_id'],
        'doctor'       => [Doctor::class, DoctorOwner::class, 'doctor_id'],
        'specialist'   => [Specialist::class, SpecialistOwner::class, 'specialist_id'],
    ];

    private const UPLOAD_DIR = 'gallery';

    public function upload(Request $request, string $type, int $id)
    {
        [$entity, ] = $this->resolveAuthorized($type, $id);

        $limit = $entity->galleryPhotoLimitForOwner();
        $current = $entity->galleryPhotosCount();
        $slotsLeft = max(0, $limit - $current);

        if ($slotsLeft === 0) {
            $message = $limit === 1
                ? 'Бесплатно доступна только 1 фотография. Чтобы загружать больше (до 15), подключите рекламный пакет.'
                : 'Достигнут лимит фотографий (' . $limit . ').';

            return response()->json(['message' => $message], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request->validate([
            'photos'   => ['required', 'array', 'min:1'],
            'photos.*' => ['image', 'max:8192'],
        ]);

        $files = array_slice($request->file('photos'), 0, $slotsLeft);

        $maxOrder = (int) $entity->photos()->max('sort_order');

        $created = [];
        foreach ($files as $file) {
            $maxOrder++;
            $path = $file->store(self::UPLOAD_DIR, 'public');

            $photo = $entity->photos()->create([
                'path'       => $path,
                'sort_order' => $maxOrder,
            ]);

            $created[] = [
                'id'  => $photo->id,
                'url' => asset('storage/' . $photo->path),
            ];
        }

        return response()->json([
            'photos'     => $created,
            'skipped'    => count($request->file('photos')) - count($files),
            'total'      => $entity->photos()->count(),
            'limit'      => $limit,
        ]);
    }

    public function destroy(string $type, int $id, int $photoId)
    {
        [$entity, ] = $this->resolveAuthorized($type, $id);

        $photo = $entity->photos()->where('id', $photoId)->firstOrFail();

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, string $type, int $id)
    {
        [$entity, ] = $this->resolveAuthorized($type, $id);

        $data = $request->validate([
            'order'   => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        $ownedIds = $entity->photos()->pluck('id')->all();

        foreach ($data['order'] as $index => $photoId) {
            if (!in_array((int) $photoId, $ownedIds, true)) {
                continue;
            }
            Photo::where('id', $photoId)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Находит сущность по типу/id и проверяет, что текущий пользователь
     * является её создателем либо подтверждённым владельцем.
     *
     * @return array{0: \Illuminate\Database\Eloquent\Model, 1: string}
     */
    private function resolveAuthorized(string $type, int $id): array
    {
        abort_unless(array_key_exists($type, self::TYPES), 404);

        [$modelClass, $ownerClass, $ownerForeignKey] = self::TYPES[$type];

        $entity = $modelClass::findOrFail($id);
        $user = Auth::user();
        abort_unless($user, 401);

        $isCreator = (int) ($entity->created_by ?? 0) === (int) $user->id;

        $isVerifiedOwner = $ownerClass::where('user_id', $user->id)
            ->where($ownerForeignKey, $entity->id)
            ->where('is_confirmed', true)
            ->exists();

        abort_unless($isCreator || $isVerifiedOwner, 403, 'Вы не являетесь владельцем этой карточки.');

        return [$entity, $type];
    }
}
