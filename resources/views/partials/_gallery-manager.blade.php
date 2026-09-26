@php
    // $entity — модель с трейтом HasGalleryPhotos
    // $type   — 'organization' | 'clinic' | 'doctor' | 'specialist'
    $limit = $entity->galleryPhotoLimitForOwner();
    $baseUrl = route('account.gallery.upload', ['type' => $type, 'id' => $entity->id]);
    // upload и base совпадают по префиксу — используем upload-роут и для базового пути удаления
    $baseUrlForDelete = url("/account/gallery/{$type}/{$entity->id}");
    $reorderUrl = route('account.gallery.reorder', ['type' => $type, 'id' => $entity->id]);
    $hasPromo = $entity->creator && $entity->creator->hasPromoPackage();
@endphp

<div class="gallery-manager"
     data-upload-url="{{ $baseUrl }}"
     data-base-url="{{ $baseUrlForDelete }}"
     data-reorder-url="{{ $reorderUrl }}"
     data-limit="{{ $limit }}">

    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-1">
        <label class="form-label mb-0 fw-semibold">
            Фотографии <span class="gallery-manager-counter text-muted">{{ $entity->galleryPhotosCount() }}/{{ $limit }}</span>
        </label>
        @unless($hasPromo)
            <small class="text-muted">Больше 1 фото — с рекламным пакетом</small>
        @endunless
    </div>

    <div class="gallery-manager-grid">
        @foreach($entity->photos as $photo)
            <div class="gallery-manager-item" draggable="true" data-photo-id="{{ $photo->id }}">
                <img src="{{ asset('storage/' . $photo->path) }}" alt="">
                <button type="button" class="gallery-manager-remove" aria-label="Удалить фото">&times;</button>
            </div>
        @endforeach
    </div>

    <label class="btn btn-outline-primary btn-sm mt-2 gallery-manager-add {{ $entity->galleryPhotosCount() >= $limit ? 'd-none' : '' }}">
        + Добавить фото
        <input type="file" accept="image/*" multiple class="d-none gallery-manager-input">
    </label>
    <div class="gallery-manager-msg small text-danger mt-1"></div>
</div>
