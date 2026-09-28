@php
    // $entity — модель с трейтом HasGalleryPhotos (Organization|Clinic|Doctor|Specialist)
    // $alt    — alt для изображения
    // $imgStyle — inline-стили для <img> (под конкретную карточку)
    // Здесь показываются ТОЛЬКО фото из галереи; логотип/фото профиля выводятся в шапке страницы.
    $photos = $entity->photos;
    $photoUrls = $photos->pluck('path')->map(fn ($p) => asset('storage/' . $p))->values();
    $extraCount = $photos->count() - 1;
@endphp

@if($photoUrls->isNotEmpty())
    <img src="{{ $photoUrls->first() }}"
         alt="{{ $alt ?? '' }}"
         class="js-gallery-cover"
         style="{{ $imgStyle ?? 'width:100%;max-width:280px;border-radius:10px;object-fit:contain;cursor:zoom-in;' }}"
         data-photos='@json($photoUrls)'>

    @if($extraCount > 0)
        <div class="js-gallery-cover text-center small text-primary mt-1" style="cursor:pointer;" data-photos='@json($photoUrls)'>
            Смотреть ещё {{ $extraCount }} {{ \App\Support\AgeFormatter::plural($extraCount, 'фотографию', 'фотографии', 'фотографий') }}
        </div>
    @endif
@endif
