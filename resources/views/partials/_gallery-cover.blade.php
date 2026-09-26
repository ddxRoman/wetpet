@php
    // $entity      — модель с трейтом HasGalleryPhotos (Organization|Clinic|Doctor|Specialist)
    // $fallbackUrl — картинка по умолчанию (лого/фото), если галерея пуста
    // $alt         — alt для изображения
    // $imgStyle    — inline-стили для <img> (под конкретную карточку)
    $photos = $entity->photos;
    $coverUrl = $photos->first() ? asset('storage/' . $photos->first()->path) : $fallbackUrl;
    $extraCount = max(0, $photos->count() - 1);
    $photoUrls = $photos->pluck('path')->map(fn ($p) => asset('storage/' . $p))->values();
    if ($photoUrls->isEmpty()) {
        $photoUrls = collect([$fallbackUrl]);
    }
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
