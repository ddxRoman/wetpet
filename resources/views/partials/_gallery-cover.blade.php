@php
    // $entity      — модель с трейтом HasGalleryPhotos (Organization|Clinic|Doctor|Specialist)
    // $fallbackUrl — логотип/фото профиля. ВСЕГДА показывается как обложка —
    //                фото из галереи её не подменяют, а доступны через лайтбокс.
    // $alt         — alt для изображения
    // $imgStyle    — inline-стили для <img> (под конкретную карточку)
    $photos = $entity->photos;
    $coverUrl = $fallbackUrl;
    $extraCount = $photos->count();
    $galleryUrls = $photos->pluck('path')->map(fn ($p) => asset('storage/' . $p))->values();
    // В лайтбоксе логотип идёт первым, дальше — фото галереи.
    $photoUrls = collect([$fallbackUrl])->merge($galleryUrls)->values();
@endphp

<img src="{{ $coverUrl }}"
     alt="{{ $alt ?? '' }}"
     class="js-gallery-cover"
     style="{{ $imgStyle ?? 'width:100%;max-width:280px;border-radius:10px;object-fit:contain;cursor:zoom-in;' }}"
     data-photos='@json($photoUrls)'>

@if($extraCount > 0)
    <div class="js-gallery-cover text-center small text-primary mt-1" style="cursor:pointer;" data-photos='@json($photoUrls)'>
        Смотреть ещё {{ $extraCount }} {{ \App\Support\AgeFormatter::plural($extraCount, 'фотографию', 'фотографии', 'фотографий') }}
    </div>
@endif
