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

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden;" data-zoom-scope>
        <div class="row g-0">
            {{-- Главное фото --}}
            <div class="col-md-6">
                <a href="{{ $gallery[0] }}" class="js-zoom d-block" data-title="{{ $pet->name }}" title="Увеличить фото">
                    <img src="{{ $gallery[0] }}"
                         alt="{{ $pet->name }}"
                         class="w-100{{ $pet->death_date ? ' pet-deceased' : '' }}"
                         style="height: 360px; object-fit: cover; cursor: zoom-in;">
                </a>
            </div>

            {{-- Информация --}}
            <div class="col-md-6">
                <div class="card-body p-4" data-zoom-info>
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
        <div class="row g-2 mb-5" data-zoom-scope>
            @foreach($gallery->slice(1) as $url)
                <div class="col-6 col-md-3">
                    <a href="{{ $url }}" class="js-zoom d-block" data-title="{{ $pet->name }}" title="Увеличить фото">
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

@endsection
