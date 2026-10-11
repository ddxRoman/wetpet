@extends('layouts.catalog')

@section('content')

@php
    $genderLabels = [
        'male'   => 'Самец',
        'female' => 'Самка',
    ];

    // Основное фото (или дефолтная картинка по виду) + дополнительные
    $gallery = collect([$pet->photo_url])
        ->merge($pet->photos->map(fn ($p) => $p->url))
        ->values();

    $ownerAvatar = $owner->avatar
        ? asset('storage/' . $owner->avatar)
        : asset('storage/avatars/default/default_avatar.webp');
@endphp

<div class="container mt-5 mb-5" style="max-width: 900px;">

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;">
        <div class="row g-0">
            {{-- Главное фото --}}
            <div class="col-md-6">
                <a href="{{ $gallery[0] }}" class="pet-lb d-block" data-index="0" title="Увеличить фото">
                    <img src="{{ $gallery[0] }}"
                         alt="{{ $pet->name }}"
                         class="w-100{{ $pet->death_date ? ' pet-deceased' : '' }}"
                         style="height: 360px; object-fit: cover; cursor: zoom-in;">
                </a>
            </div>

            {{-- Информация --}}
            <div class="col-md-6">
                <div class="card-body p-4">
                    <h1 class="h3 fw-bold mb-2">{{ $pet->name }}</h1>

                    @if($pet->animal)
                        <div class="text-muted mb-1">
                            {{ $pet->animal->species }}@if($pet->animal->breed) ({{ $pet->animal->breed }})@endif
                        </div>
                    @endif

                    @if(isset($genderLabels[$pet->gender]))
                        <div class="text-muted mb-1">{{ $genderLabels[$pet->gender] }}</div>
                    @endif

                    @if($pet->death_date)
                        <div class="text-muted mb-1">Дата смерти: {{ $pet->death_date->format('d.m.Y') }}</div>
                        @if($pet->age_label)
                            <div class="text-muted mb-1">Возраст на момент смерти: {{ $pet->age_label }}</div>
                        @endif
                    @elseif($pet->age_label)
                        <div class="text-muted mb-1">Возраст: {{ $pet->age_label }}</div>
                    @endif

                    @if($pet->caption)
                        <p class="mt-3 mb-0" style="white-space: pre-line;">{{ $pet->caption }}</p>
                    @endif

                    <hr>

                    <a href="{{ route('user.profile', $owner->id) }}" class="d-flex align-items-center gap-2 text-decoration-none text-reset">
                        <img src="{{ $ownerAvatar }}" alt="{{ $owner->name }}" width="40" height="40"
                             class="rounded-circle" style="object-fit: cover;">
                        <span>
                            <span class="small text-muted d-block">Владелец</span>
                            <span class="fw-semibold">{{ $owner->name }}</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Остальные фото --}}
    @if($gallery->count() > 1)
        <h2 class="h4 fw-bold mb-3">Фото</h2>
        <div class="row g-2 mb-5">
            @foreach($gallery->slice(1) as $i => $url)
                <div class="col-6 col-md-3">
                    <a href="{{ $url }}" class="pet-lb d-block" data-index="{{ $i }}" title="Увеличить фото">
                        <img src="{{ $url }}" alt="{{ $pet->name }}"
                             class="w-100 rounded{{ $pet->death_date ? ' pet-deceased' : '' }}"
                             style="height: 180px; object-fit: cover; cursor: zoom-in;">
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    <a href="{{ route('user.profile', $owner->id) }}" class="btn btn-light" style="border-radius: 12px;">← К профилю владельца</a>
</div>

{{-- Просмотр фото с листанием --}}
<div id="petLightbox" class="pet-lightbox" hidden>
    <button type="button" class="pet-lightbox__close" aria-label="Закрыть">&times;</button>
    <button type="button" class="pet-lightbox__nav pet-lightbox__prev" aria-label="Предыдущее фото">&#10094;</button>
    <img class="pet-lightbox__img" src="" alt="{{ $pet->name }}">
    <button type="button" class="pet-lightbox__nav pet-lightbox__next" aria-label="Следующее фото">&#10095;</button>
    <div class="pet-lightbox__counter"></div>
</div>

<style>
    .pet-lightbox { position: fixed; inset: 0; z-index: 10050; background: rgba(0,0,0,.92);
        display: flex; align-items: center; justify-content: center; }
    .pet-lightbox[hidden] { display: none; }
    .pet-lightbox__img { max-width: 90vw; max-height: 85vh; object-fit: contain; border-radius: 8px; user-select: none; }
    .pet-lightbox__close, .pet-lightbox__nav { position: absolute; background: rgba(255,255,255,.15); color: #fff;
        border: 0; cursor: pointer; line-height: 1; }
    .pet-lightbox__close { top: 16px; right: 20px; width: 44px; height: 44px; border-radius: 50%; font-size: 30px; }
    .pet-lightbox__nav { top: 50%; transform: translateY(-50%); width: 48px; height: 64px; border-radius: 10px; font-size: 24px; }
    .pet-lightbox__prev { left: 16px; }
    .pet-lightbox__next { right: 16px; }
    .pet-lightbox__close:hover, .pet-lightbox__nav:hover { background: rgba(255,255,255,.3); }
    .pet-lightbox__counter { position: absolute; bottom: 18px; left: 50%; transform: translateX(-50%); color: #fff; font-size: 14px; }
    @media (max-width: 576px) { .pet-lightbox__nav { width: 38px; height: 54px; } }
</style>

<script>
(function () {
    const photos = @json($gallery->values());
    const box = document.getElementById('petLightbox');
    if (!box || !photos.length) return;

    const img = box.querySelector('.pet-lightbox__img');
    const counter = box.querySelector('.pet-lightbox__counter');
    const prev = box.querySelector('.pet-lightbox__prev');
    const next = box.querySelector('.pet-lightbox__next');
    let index = 0;

    function show(i) {
        index = (i + photos.length) % photos.length;
        img.src = photos[index];
        counter.textContent = (index + 1) + ' / ' + photos.length;
        const single = photos.length < 2;
        prev.style.display = next.style.display = single ? 'none' : '';
        counter.style.display = single ? 'none' : '';
    }
    function open(i) {
        show(i);
        box.hidden = false;
        document.body.style.overflow = 'hidden';
    }
    function close() {
        box.hidden = true;
        img.src = '';
        document.body.style.overflow = '';
    }

    // Порядок ссылок на странице совпадает с порядком в массиве photos: главное фото, затем остальные
    document.querySelectorAll('.pet-lb').forEach(function (a, n) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            open(n);
        });
    });

    prev.addEventListener('click', function (e) { e.stopPropagation(); show(index - 1); });
    next.addEventListener('click', function (e) { e.stopPropagation(); show(index + 1); });
    box.querySelector('.pet-lightbox__close').addEventListener('click', close);
    box.addEventListener('click', function (e) { if (e.target === box) close(); });

    document.addEventListener('keydown', function (e) {
        if (box.hidden) return;
        if (e.key === 'Escape') close();
        else if (e.key === 'ArrowLeft') show(index - 1);
        else if (e.key === 'ArrowRight') show(index + 1);
    });

    // Свайп на телефоне
    let startX = null;
    box.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
    box.addEventListener('touchend', function (e) {
        if (startX === null) return;
        const dx = e.changedTouches[0].clientX - startX;
        startX = null;
        if (Math.abs(dx) > 50) show(dx < 0 ? index + 1 : index - 1);
    });
})();
</script>

@endsection
