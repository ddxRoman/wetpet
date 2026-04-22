@extends('layouts.app')

@section('content')
@php
    if (!isset($doctor)) {
        abort(404);
    }

    $photo = $doctor->photo && Storage::disk('public')->exists($doctor->photo)
        ? asset('storage/'.$doctor->photo)
        : asset('storage/doctors/default-doctor.webp');

    // Формируем адрес: Город, Название организации
    $addressParts = array_filter([
        $doctor->city->name ?? '',
        $doctor->organization->name ?? '',
    ]);

    $mapQuery = urlencode(implode(', ', $addressParts));
    $tab = request('tab', 'info');
@endphp

@include('layouts.header')

<main class="flex-grow-1 container mt-5">

    {{-- КНОПКА НАЗАД --}}
    <div class="mb-4">
        <a href="{{ route('specialists.index') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 shadow-sm back-to-catalog">
            <img src="{{ asset('storage/icon/button/arrow-back.svg') }}" width="22" alt="back">
            В каталог
        </a>
    </div>

    {{-- ШАПКА --}}
    <div class="d-flex align-items-start flex-wrap mb-4">
        <img src="{{ $photo }}"
             style="width:90px;height:90px;border-radius:10px;object-fit:cover"
             class="me-3">

        <div class="flex-grow-1">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <h1 class="fw-bold m-0" style="font-size: 1.75rem;">{{ $doctor->name }}</h1>
                
                @if($doctor->exotic_animals === 'Да')
                    <span class="badge bg-warning text-dark" title="Работает с экзотическими животными">🦎</span>
                @endif

{{-- Рейтинг --}}
@php
    $reviewCount = $doctor->reviews_count;
    // Округляем средний рейтинг до 1 знака после запятой
    $averageRating = round($doctor->reviews_avg_rating, 1);
@endphp

<div class="rating-badge-container d-flex align-items-center px-2 py-1 rounded shadow-sm" 
     style="background-color: #fff8e1; border: 1px solid #ffe082;">
    
    <div class="d-flex align-items-center me-2">
        @for ($i = 1; $i <= 5; $i++)
            {{-- Если текущая звезда меньше или равна рейтингу — рисуем активную --}}
            <img src="{{ asset('storage/icon/button/' . ($i <= round($averageRating) ? 'award-stars_active.svg' : 'award-stars_disable.svg')) }}"
                 width="18" 
                 alt="звезда">
        @endfor
    </div>

    @if($reviewCount > 0)
        <span class="fw-bold text-dark me-1" style="font-size: 0.9rem;">
            {{ number_format($averageRating, 1) }}
        </span>
        <span class="text-muted small">
            ({{ $reviewCount }} {{ trans_choice('отзыв|отзыва|отзывов', $reviewCount, [], 'ru') }})
        </span>
    @else
        <span class="text-muted small">Нет отзывов</span>
    @endif
</div>
            </div>

            <div class="text-muted">
                {{ $doctor->specialization }}
            </div>
        </div>
    </div>

    {{-- ТАБЫ --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'info' ? 'active' : '' }}" href="?tab=info">Информация</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'contacts' ? 'active' : '' }}" href="?tab=contacts">Контакты</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'services' ? 'active' : '' }}" href="?tab=services">Услуги</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'reviews' ? 'active' : '' }}" href="?tab=reviews">Отзывы</a>
        </li>
    </ul>

    <div class="row">
        <div class="col-lg-8">
            {{-- ВАЖНО: Проверь наличие этих файлов в папке resources/views/pages/specialists/tabs/ --}}
            @if($tab === 'info')
                @include('pages.specialists.tabs.info', ['doctor' => $doctor])
            @endif

            @if($tab === 'contacts')
                @include('pages.specialists.tabs.contacts', ['doctor' => $doctor])
            @endif

            @if($tab === 'services')
                @include('pages.specialists.tabs.services', ['doctor' => $doctor])
            @endif

            @if($tab === 'reviews')
                @include('pages.specialists.tabs.reviews', ['doctor' => $doctor])
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <img src="{{ $photo }}"
                         style="width:100%;max-width:280px;border-radius:10px;object-fit:cover">
                </div>
                @if($doctor->organization)
                    <div class="card-footer bg-white">
                        <small class="text-muted">Место работы:</small>
                        <div class="fw-bold">{{ $doctor->organization->name }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</main>

<footer class="footer-fullwidth mt-auto w-100">
    @include('layouts.footer')
</footer>
@endsection