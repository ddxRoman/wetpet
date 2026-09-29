<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\ClinicOwner;
use App\Models\Doctor;
use App\Models\EntityPhoto;
use App\Models\Organization;
use App\Models\OrganizationOwner;
use App\Models\OwnerClaimMessage;
use App\Models\OwnershipDocument;
use App\Models\Price;
use App\Models\Promotion;
use App\Models\Review;
use App\Models\Specialist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Перенос карточки между разделами «Организации» и «Клиники».
 *
 * Клиники и организации хранятся в разных таблицах (clinics / organizations),
 * поэтому смена сферы деятельности на/с «Ветеринарная клиника» — это перенос
 * записи: создаём карточку в целевой таблице с теми же данными и тем же slug
 * (меняется только префикс URL), перевешиваем все связанные данные
 * (фото, цены, отзывы, акции, владельцы, документы и переписка по верификации)
 * и удаляем старую запись.
 */
class EntityTypeConverter
{
    /** Поля, одинаковые у clinics и organizations. */
    private const COPY_FIELDS = [
        'is_verified', 'created_by', 'name', 'country', 'region', 'city',
        'street', 'house', 'address_comment', 'logo', 'description',
        'phone1', 'phone2', 'email', 'telegram', 'whatsapp', 'max',
        'website', 'schedule', 'workdays', 'seo_title', 'seo_description',
    ];

    /**
     * Организация → Клиника.
     */
    public function organizationToClinic(Organization $organization): Clinic
    {
        return DB::transaction(function () use ($organization) {
            $clinic = $this->createCopy(Clinic::class, $organization);

            $this->moveRelations($organization, $clinic);

            $this->moveOwners(
                OrganizationOwner::class, 'organization_id', $organization->id,
                ClinicOwner::class,       'clinic_id',       $clinic->id
            );

            // У клиники нет «специалистов организации» — просто снимаем привязку.
            Specialist::where('organization_id', $organization->id)->update(['organization_id' => null]);

            $organization->delete();

            return $clinic;
        });
    }

    /**
     * Клиника → Организация с указанной сферой деятельности.
     */
    public function clinicToOrganization(Clinic $clinic, int $fieldOfActivityId): Organization
    {
        return DB::transaction(function () use ($clinic, $fieldOfActivityId) {
            $organization = $this->createCopy(Organization::class, $clinic, [
                'field_of_activity_id' => $fieldOfActivityId,
            ]);

            $this->moveRelations($clinic, $organization);

            $this->moveOwners(
                ClinicOwner::class,       'clinic_id',       $clinic->id,
                OrganizationOwner::class, 'organization_id', $organization->id
            );

            // Врачи клиники остаются в системе, но без привязки к клинике
            // (так же, как при удалении карточки владельцем).
            Doctor::where('clinic_id', $clinic->id)->update(['clinic_id' => null]);

            // Награды и связь с услугами (clinic_service) удалятся каскадом вместе с клиникой.
            $clinic->delete();

            return $organization;
        });
    }

    /**
     * Создаёт запись в целевой таблице с теми же данными и тем же slug
     * (или с числовым суффиксом, если такой slug там уже занят).
     *
     * Создаём без событий модели: иначе EntityCreationObserver сбросил бы
     * is_verified у карточки, созданной «обычным пользователем», и разослал бы
     * админам уведомление «Новая запись», а Organization::creating перезаписал бы slug.
     */
    private function createCopy(string $targetClass, Model $source, array $extra = []): Model
    {
        /** @var Model $target */
        $target = new $targetClass();

        $target->forceFill(Arr::only($source->getAttributes(), self::COPY_FIELDS) + $extra);
        $target->slug = $this->uniqueSlug($targetClass, (string) $source->slug, $source->name);

        // Сохраняем исходные даты создания/обновления.
        $target->created_at = $source->created_at;
        $target->updated_at = $source->updated_at;

        $targetClass::withoutEvents(fn () => $target->save());

        return $target;
    }

    private function uniqueSlug(string $targetClass, string $slug, ?string $fallbackName): string
    {
        $base = $slug !== '' ? $slug : \Illuminate\Support\Str::slug((string) $fallbackName);
        if ($base === '') {
            $base = 'card-' . time();
        }

        $candidate = $base;
        $i = 1;
        while ($targetClass::where('slug', $candidate)->exists()) {
            $candidate = $base . '-' . $i++;
        }

        return $candidate;
    }

    /**
     * Перевешивает полиморфные данные со старой карточки на новую.
     */
    private function moveRelations(Model $from, Model $to): void
    {
        $fromType = get_class($from);
        $toType   = get_class($to);

        $map = [
            [EntityPhoto::class, 'photoable'],
            [Price::class,       'priceable'],
            [Review::class,      'reviewable'],
            [Promotion::class,   'promotable'],
        ];

        foreach ($map as [$model, $prefix]) {
            $model::where("{$prefix}_type", $fromType)
                ->where("{$prefix}_id", $from->id)
                ->update([
                    "{$prefix}_type" => $toType,
                    "{$prefix}_id"   => $to->id,
                ]);
        }
    }

    /**
     * Переносит записи владения (со всеми пользователями), вместе с документами
     * верификации и перепиской по заявке.
     */
    private function moveOwners(
        string $fromOwnerClass, string $fromFk, int $fromId,
        string $toOwnerClass,   string $toFk,   int $toId
    ): void {
        foreach ($fromOwnerClass::where($fromFk, $fromId)->get() as $row) {
            $new = new $toOwnerClass();
            $new->forceFill([
                'user_id'       => $row->user_id,
                $toFk           => $toId,
                'is_confirmed'  => $row->is_confirmed,
                'is_rejected'   => $row->is_rejected,
                'rejected_at'   => $row->rejected_at,
                'admin_comment' => $row->admin_comment,
                'created_at'    => $row->created_at,
                'updated_at'    => $row->updated_at,
            ])->save();

            OwnershipDocument::where('ownerable_type', $fromOwnerClass)
                ->where('ownerable_id', $row->id)
                ->update(['ownerable_type' => $toOwnerClass, 'ownerable_id' => $new->id]);

            OwnerClaimMessage::where('claimable_type', $fromOwnerClass)
                ->where('claimable_id', $row->id)
                ->update(['claimable_type' => $toOwnerClass, 'claimable_id' => $new->id]);

            $row->delete();
        }
    }
}
