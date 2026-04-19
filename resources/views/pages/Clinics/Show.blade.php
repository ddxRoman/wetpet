@extends('layouts.app')

@section('content')
@php

    if (!isset($clinic)) {
        abort(404);
    }

    // Собираем адрес из отдельных полей
    $addressParts = array_filter([
        $clinic->city,
        $clinic->street ? $clinic->street : null,
        $clinic->house
    ]);
    
    // Записываем результат в переменную (или создаем новую, например $fullAddress)
    $clinicAddress = implode(', ', $addressParts);



    $logo = !empty($clinic->logo)
        ? asset('storage/' . $clinic->logo)
        : asset('storage/clinics/logo/default.webp');

    $tab = request('tab', 'contacts');

    // Логика рейтинга
    use App\Models\Review;
    $reviews = Review::where('reviewable_id', $clinic->id)
        ->where('reviewable_type', \App\Models\Clinic::class)
        ->get();
    $reviewCount = $reviews->count();
    $averageRating = $reviewCount > 0 ? round($reviews->avg('rating'), 1) : null;
@endphp

@include('layouts.header')

<main class="flex-grow-1 container mt-5">

    {{-- КНОПКА НАЗАД --}}
    <div class="mb-4">
        <div class="mb-3">
            <a href="{{ route('clinics.index') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 shadow-sm back-to-catalog"
               title="Вернутся к каталогу всех клиник города">
                <img src="{{ asset('storage/icon/button/arrow-back.svg') }}" width="22" alt="paw">
                В каталог
            </a>
        </div>
    </div>

    {{-- ШАПКА --}}
    <div class="d-flex align-items-start flex-wrap mb-4">
        <img src="{{ $logo }}" 
             style="width:90px;height:90px;border-radius:10px;object-fit:cover" 
             class="me-3 mb-3 mb-md-0">

        <div class="flex-grow-1">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <h1 class="fw-bold m-0" style="font-size: 1.75rem;">{{ $clinic->name }}</h1>

                {{-- ⭐ Блок рейтинга в одну строку с бежевым фоном --}}
                <div class="rating-badge-container d-flex align-items-center px-2 py-1 rounded shadow-sm" style="background-color: #fff8e1; border: 1px solid #ffe082;">
                    <div class="d-flex align-items-center me-2">
                        @for ($i = 1; $i <= 5; $i++)
                            <img src="{{ asset('storage/icon/button/' . ($i <= ($averageRating ?? 0) ? 'award-stars_active.svg' : 'award-stars_disable.svg')) }}"
                                 width="18" alt="звезда">
                        @endfor
                    </div>
                    @if($reviewCount > 0)
                        <span class="fw-bold text-dark me-1" style="font-size: 0.9rem;">{{ $averageRating }}</span>
                        <span class="text-muted small">({{ $reviewCount }} {{ $reviewCount % 10 == 1 && $reviewCount % 100 != 11 ? 'отзыв' : 'отзывов' }})</span>
                    @else
                        <span class="text-muted small">Нет отзывов</span>
                    @endif
                </div>
            </div>
            
            <div class="text-muted">
                {{ $clinicAddress ?? 'Адрес не указан' }}
            </div>
        </div>
    </div>

    {{-- ТАБЫ --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'contacts' ? 'active' : '' }}" title="Просмотреть контакты" href="?tab=contacts">Контакты</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'services' ? 'active' : '' }}" title="Открыть список услуг" href="?tab=services">Услуги</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'reviews' ? 'active' : '' }}" title="Прочитать отзывы о клинике" href="?tab=reviews">Отзывы</a>
        </li>
    </ul>

    <div class="row">
        <div class="col-lg-8">
            {{-- Контент вкладок вынесен в отдельные файлы для соблюдения структуры --}}
            @if($tab === 'contacts')
                @include('pages.clinics.tabs.contacts', ['clinic' => $clinic])
            @endif

            @if($tab === 'services')
                @include('pages.clinics.tabs.services', ['clinic' => $clinic])
            @endif

            @if($tab === 'reviews')
                @include('pages.clinics.tabs.reviews', ['clinic_id' => $clinic->id])
            @endif
        </div>

        <div class="col-lg-4">
            {{-- Правая колонка с логотипом как в show2 --}}
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <img src="{{ $logo }}"
                         style="width:100%;max-width:280px;border-radius:10px;object-fit:cover">
                </div>
            </div>
        </div>
    </div>

    {{-- СПИСОК ДОКТОРОВ (нижняя секция) --}}
    <div class="mb-4 mt-5">
        <h2 class="fs-5 fw-semibold mb-3">Доктора клиники</h2>
        @php
            $doctors = \App\Models\Doctor::where('clinic_id', $clinic->id)->get();
        @endphp

        <div class="row g-3">
            @forelse ($doctors as $doctor)
                @php
                    $doctorAvgRating = $doctor->reviews()->avg('rating') ? number_format($doctor->reviews()->avg('rating'), 1) : '0.0';
                @endphp
                <div class="col-md-6 col-lg-4 col-sm-6">
                    <a href="{{ route('doctors.show', $doctor->slug) }}" class="text-decoration-none text-reset">
                        <div class="card h-100 shadow-sm border-0 position-relative doctor-card">
                            <div class="rating-badge">
                                <img width="24px" src="{{ asset('storage/icon/stars/doctors_stars.png') }}" alt="Рейтинг">
                                <span class="rating-value">{{ $doctorAvgRating }}</span>
                            </div>
                            <div class="card-body text-center">
                                <img src="{{ $doctor->photo ? asset('/storage/' . $doctor->photo) : asset('/storage/doctors/default-doctor.webp') }}"
                                     alt="{{ $doctor->name }}" class="doctor-photo mb-3">
                                <h5 class="card-title mb-1">{{ $doctor->name }}</h5>
                                <p class="text-muted mb-2">{{ $doctor->specialization ?? 'Ветеринар' }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <p class="text-muted">Доктора этой клинике еще не указаны.</p>
            @endforelse
        </div>
    </div>

</main>



<footer class="footer-fullwidth mt-auto w-100">
    @include('layouts.footer')
</footer>
@endsection