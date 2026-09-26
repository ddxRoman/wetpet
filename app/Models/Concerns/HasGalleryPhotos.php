<?php

namespace App\Models\Concerns;

use App\Models\Photo;

/**
 * Общая логика фотогалереи для Organization, Clinic, Doctor, Specialist.
 *
 * Правила пакетов:
 *  - Бесплатно: 1 фотография.
 *  - С активным рекламным пакетом владельца (User::hasPromoPackage()): до 15 фотографий.
 *  - Из админки (Filament) ограничение пакетом не действует, но абсолютный
 *    максимум в 15 штук — общий для всех (см. self::MAX_PHOTOS).
 */
trait HasGalleryPhotos
{
    public function photos()
    {
        return $this->morphMany(Photo::class, 'photoable')->orderBy('sort_order');
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

        return $first ? asset('storage/' . $first->path) : null;
    }

    public function galleryPhotosCount(): int
    {
        return $this->photos->count();
    }
}
