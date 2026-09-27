<?php

namespace App\Models\Concerns;

use App\Models\EntityPhoto;

/**
 * Общая логика фотогалереи для Organization/Clinic/Doctor/Specialist.
 *
 * Использует уже существующую полиморфную таблицу entity_photos
 * (модель App\Models\EntityPhoto) — ту же, в которую пишет реальный
 * личный кабинет владельца (OwnerCabinetController::uploadPhoto).
 *
 * Правила пакетов:
 *  - Бесплатно: 1 фотография.
 *  - С активным рекламным пакетом владельца (User::hasPromoPackage()): до 15 фотографий.
 *  - Из админки (Filament) ограничение пакетом не действует, но абсолютный
 *    максимум в 15 штук — общий для всех (см. self::maxGalleryPhotos()).
 */
trait HasGalleryPhotos
{
    public function photos()
    {
        return $this->morphMany(EntityPhoto::class, 'photoable')->orderBy('sort_order');
    }

    /**
     * Абсолютный максимум фотографий — не превышается никем, даже из админки.
     */
    public static function maxGalleryPhotos(): int
    {
        return 15;
    }

    /**
     * Сколько фотографий доступно владельцу карточки (личный кабинет),
     * с учётом наличия активного рекламного пакета у создателя записи.
     */
    public function galleryPhotoLimitForOwner(): int
    {
        $creator = $this->creator ?? null;

        if ($creator && method_exists($creator, 'hasPromoPackage') && $creator->hasPromoPackage()) {
            return self::maxGalleryPhotos();
        }

        return 1;
    }

    /**
     * URL обложки галереи (первое фото по сортировке) или null, если фото нет —
     * тогда вызывающий код должен подставить лого/дефолтное изображение.
     */
    public function galleryCoverUrl(): ?string
    {
        $first = $this->photos->first();

        return $first ? $first->url : null;
    }

    public function galleryPhotosCount(): int
    {
        return $this->photos->count();
    }
}
