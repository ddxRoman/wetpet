@extends('layouts.app')

@section('content')
@php
    $logo = $organization->logo && Storage::disk('public')->exists($organization->logo)
        ? asset('storage/'.$organization->logo)
        : asset('storage/organizations/default-org.webp');

    $addressParts = array_filter([
        $organization->city,
        $organization->street,
        $organization->house,
    ]);

    $mapQuery = urlencode(implode(', ', $addressParts));
    $tab = request('tab', 'info');
@endphp

@include('layouts.header')

<main class="flex-grow-1 container mt-5">

    {{-- КНОПКА НАЗАД --}}
    <div class="mb-4">
        <a href="{{ route('organizations.index') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 shadow-sm">
            <img src="{{ asset('storage/icon/button/arrow-back.svg') }}" width="22" alt="back">
            В каталог
        </a>
    </div>

    {{-- ШАПКА --}}
    <div class="d-flex align-items-start flex-wrap mb-4">
        <img src="{{ $logo }}"
             style="width:120px;height:120px;border-radius:10px;object-fit:contain; background: #fff"
             class="me-3 border p-2">

        <div class="flex-grow-1">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <h1 class="fw-bold m-0" style="font-size: 1.75rem;">{{ $organization->name }}</h1>
                
                {{-- Рейтинг --}}
                @php
                    $reviewCount = $organization->reviews_count ?? 0;
                    $averageRating = round($organization->reviews_avg_rating ?? 0, 1);
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
                        <span class="fw-bold text-dark me-1" style="font-size: 0.9rem;">{{ $averageRating }}</span>
                        <span class="text-muted small">({{ $reviewCount }})</span>
                    @else
                        <span class="text-muted small">Нет отзывов</span>
                    @endif
                </div>
            </div>

            <div class="text-muted mb-2">
                {{-- Показываем название типа из связи --}}
                {{ $organization->activityType->name ?? 'Зооорганизация' }}
            </div>
            
            <div class="small text-muted">
                <i class="bi bi-geo-alt"></i> {{ implode(', ', $addressParts) }}
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
            <a class="nav-link {{ $tab === 'reviews' ? 'active' : '' }}" href="?tab=reviews">Отзывы</a>
        </li>
    </ul>

    <div class="row">
        <div class="col-lg-8">
            {{-- Контент табов (убедитесь, что создали эти файлы в папке organizations/tabs) --}}
            @if($tab === 'info')
                <div class="card shadow-sm border-0 p-4 mb-4">
                    <h3>О компании</h3>
                    <div class="description-text">
                        {!! nl2br(e($organization->description)) !!}
                    </div>
                </div>
            @endif

            @if($tab === 'contacts')
                <div class="card shadow-sm border-0 p-4 mb-4">
                    <h3>Контактная информация</h3>
                    <ul class="list-unstyled mt-3">
                        @if($organization->phone1) <li class="mb-2"><strong>Телефон:</strong> {{ $organization->phone1 }}</li> @endif
                        @if($organization->email) <li class="mb-2"><strong>Email:</strong> <a href="mailto:{{ $organization->email }}">{{ $organization->email }}</a></li> @endif
                        @if($organization->website) <li class="mb-2"><strong>Сайт:</strong> <a href="{{ $organization->website }}" target="_blank">{{ $organization->website }}</a></li> @endif
                    </ul>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="card-title">Режим работы</h5>
                    <p class="mb-1"><strong>Дни:</strong> {{ $organization->workdays ?? 'Не указано' }}</p>
                    <p class="mb-0"><strong>Время:</strong> {{ $organization->schedule ?? 'Не указано' }}</p>
                </div>
            </div>

            {{-- Карта --}}
            <div class="card shadow-sm border-0 overflow-hidden" style="height: 300px;">
                <iframe 
                    width="100%" 
                    height="100%" 
                    frameborder="0" 
                    style="border:0"
                    src="https://yandex.ru/map-widget/v1/?text={{ $mapQuery }}" 
                    allowfullscreen>
                </iframe>
            </div>
        </div>
    </div>
</main>

<footer class="footer-fullwidth mt-auto w-100">
    @include('layouts.footer')
</footer>
@endsection