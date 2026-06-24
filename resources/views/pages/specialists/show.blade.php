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
<div class="d-flex align-items-start justify-content-between flex-wrap mb-4">
    
    {{-- Левый блок: Фото + Инфо --}}
    <div class="d-flex align-items-start flex-wrap flex-grow-1">
        <img src="{{ $photo }}"
             style="width:90px;height:90px;border-radius:10px;object-fit:cover"
             class="me-3 border p-1">

        <div class="flex-grow-1">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <h1 class="fw-bold m-0" style="font-size: 1.75rem;">{{ $doctor->name }}</h1>
                
                @if($doctor->exotic_animals === 'Да')
                    <img src="{{ asset('storage/icon/stars/exotic.png') }}" class="exotic_icon_card" alt="Экзотическое животное" title="Этот специалист работает с экзотическими животными, такими как ящерецы, грызуны, черепахи и тд">
                @endif

                {{-- Рейтинг --}}
                @php
                    $reviewCount = $doctor->reviews_count;
                    $averageRating = round($doctor->reviews_avg_rating, 1);
                @endphp

                <div class="rating-badge-container d-flex align-items-center px-2 py-1 rounded shadow-sm" 
                     style="background-color: #fff8e1; border: 1px solid #ffe082;">
                    
                    <div class="d-flex align-items-center me-2">
                        @for ($i = 1; $i <= 5; $i++)
                            <img src="{{ asset('storage/icon/button/' . ($i <= round($averageRating) ? 'award-stars_active.svg' : 'award-stars_disable.svg')) }}"
                                 width="18" alt="звезда">
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

    {{-- Правый блок: Кнопка "Это я" --}}
    <div class="ms-md-3 mt-3 mt-md-0">
        @auth
            @php
                $alreadyOwner = \App\Models\SpecialistOwner::where('user_id', auth()->id())
                    ->where('specialist_id', $doctor->id)
                    ->first();

                // Подтверждён ли пользователь уже как ЛЮБОЙ специалист или доктор
                $isAnySpecialistConfirmed =
                    \App\Models\SpecialistOwner::where('user_id', auth()->id())->where('is_confirmed', true)->exists() ||
                    \App\Models\DoctorOwner::where('user_id', auth()->id())->where('is_confirmed', true)->exists();

                // Есть ли уже заявка на ДРУГОГО специалиста/доктора
                $hasOtherClaim =
                    \App\Models\SpecialistOwner::where('user_id', auth()->id())->where('specialist_id', '!=', $doctor->id)->exists() ||
                    \App\Models\DoctorOwner::where('user_id', auth()->id())->exists();
            @endphp

            @if($alreadyOwner && $alreadyOwner->is_confirmed)
                {{-- Эта карточка подтверждена --}}
                <span class="btn btn-success fw-bold disabled d-flex align-items-center gap-2"
                      style="border-radius: 10px; padding: 8px 16px; opacity: .7;">
                    ✓ Подтверждено
                </span>
            @elseif($alreadyOwner && !$alreadyOwner->is_confirmed)
                {{-- Заявка на эту карточку уже подана --}}
                <button class="btn btn-warning fw-bold d-flex align-items-center gap-2"
                        style="border-radius: 10px; padding: 8px 16px;"
                        data-bs-toggle="modal" data-bs-target="#claimOwnershipModal">
                    ⏳ На проверке (дополнить)
                </button>
            @elseif(!$isAnySpecialistConfirmed && !$hasOtherClaim)
                {{-- Пользователь ещё не связан ни с каким специалистом — показываем кнопку --}}
                <button class="btn btn-success fw-bold d-flex align-items-center gap-2"
                        style="border-radius: 10px; padding: 8px 16px; border-style: dashed;"
                        data-bs-toggle="modal" data-bs-target="#claimOwnershipModal">
                    <img src="{{ asset('storage/icon/button/is_me.svg') }}" width="20" alt="is_me" onerror="this.style.display='none'">
                    Это я
                </button>
            @endif
            {{-- Если isAnySpecialistConfirmed или hasOtherClaim — кнопка не показывается вовсе --}}

            @include('partials.modal-claim-ownership', ['entityType' => 'specialist', 'entityId' => $doctor->id])
        @else
            <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}"
               class="btn btn-success fw-bold d-flex align-items-center gap-2"
               style="border-radius: 10px; padding: 8px 16px; border-style: dashed;">
                Это я
            </a>
        @endauth
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