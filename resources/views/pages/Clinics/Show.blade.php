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
        : asset('storage/clinics/logo/default-clinic.webp');

    $tab = request('tab', 'info');

    // Логика рейтинга (только собственные отзывы клиники — рейтинг заведения,
    // отдельно от отзывов о конкретных врачах)
    use App\Models\Review;
    $reviews = Review::where('reviewable_id', $clinic->id)
        ->where('reviewable_type', \App\Models\Clinic::class)
        ->get();
    $reviewCount = $reviews->count();
    $averageRating = $reviewCount > 0 ? round($reviews->avg('rating'), 1) : null;

    // Счётчик у вкладки «Отзывы»: собственные отзывы клиники плюс отзывы о
    // врачах, которые работают/работали в ней — ровно то, что реально
    // показывается во вкладке «Отзывы» (см. tabs/reviews.blade.php).
    $tabReviewCount = $reviewCount
        + Review::where('workplace_type', \App\Models\Clinic::class)
        ->where('workplace_id', $clinic->id)
        ->count();
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
    <div class="d-flex align-items-start justify-content-between flex-wrap mb-4">
        
        {{-- Левый блок: Лого + Текст --}}
        <div class="d-flex align-items-start flex-wrap flex-grow-1">
            <img src="{{ $logo }}" 
                 style="width:90px;height:90px;border-radius:10px;object-fit:contain;background:#f8f9fa" 
                 class="me-3 mb-3 mb-md-0 border p-1">

            <div class="flex-grow-1">
                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                    <h1 class="fw-bold m-0" style="font-size: 1.75rem;">{{ $clinic->name }}</h1>

                    {{-- ⭐ Блок рейтинга --}}
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
                    <i class="bi bi-geo-alt"></i> {{ $clinicAddress ?? 'Адрес не указан' }}
                </div>
            </div>
        </div>

        {{-- Правый блок: Кнопка "ЭТО Я" --}}
        <div class="ms-md-3 mt-2 mt-md-0">
            @auth
                @php
                    // Если по этой карточке несколько заявок — берём подтверждённую (иначе дубль «на проверке» перекрывал бы её)
                    $alreadyOwner = \App\Models\ClinicOwner::where('user_id', auth()->id())
                        ->where('clinic_id', $clinic->id)
                        ->orderByDesc('is_confirmed')
                        ->first();
                @endphp

                {{-- «Перейти к управлению» — только когда админ подтвердил владение. Пока заявка на проверке — «Заявка на проверке (дополнить)». --}}
                @if($alreadyOwner && $alreadyOwner->is_confirmed)
                    <a href="{{ route('owner.clinic', $clinic->id) }}"
                       class="btn btn-success fw-bold d-flex align-items-center gap-2"
                       style="border-radius: 10px; padding: 8px 16px;">
                        ⚙️ Перейти к управлению
                    </a>
                @elseif($alreadyOwner && !$alreadyOwner->is_confirmed)
                    <button class="btn btn-warning fw-bold d-flex align-items-center gap-2"
                            style="border-radius: 10px; padding: 8px 16px;"
                            data-bs-toggle="modal" data-bs-target="#claimOwnershipModal">
                        ⏳ Заявка на проверке (дополнить)
                    </button>
                @else
                    <button class="btn btn-success fw-bold d-flex align-items-center gap-2"
                            style="border-radius: 10px; padding: 8px 16px; border-style: dashed;"
                            data-bs-toggle="modal" data-bs-target="#claimOwnershipModal">
                        <img src="{{ asset('storage/icon/button/is_me.svg') }}" width="20" alt="is_me" onerror="this.style.display='none'">
                        Это моя клиника
                    </button>
                @endif

                @include('partials.modal-claim-ownership', ['entityType' => 'clinic', 'entityId' => $clinic->id])
            @else
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}"
                   class="btn btn-success fw-bold d-flex align-items-center gap-2"
                   style="border-radius: 10px; padding: 8px 16px; border-style: dashed;">
                    Это моя клиника
                </a>
            @endauth
        </div>
    </div>

    {{-- АКЦИИ --}}
    @include('partials._promotions-widget', ['entity' => $clinic])

    {{-- ТАБЫ --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'info' ? 'active' : '' }}" title="Просмотреть общую информацию" href="?tab=info">Информация</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'contacts' ? 'active' : '' }}" title="Просмотреть контакты" href="?tab=contacts">Контакты</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'services' ? 'active' : '' }}" title="Открыть список услуг" href="?tab=services">Услуги</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'reviews' ? 'active' : '' }}" title="Прочитать отзывы о клинике" href="?tab=reviews">
                Отзывы <span class="badge bg-secondary rounded-pill">{{ $tabReviewCount }}</span>
            </a>
        </li>
    </ul>

    

    <div class="row">
        <div class="col-lg-8">
            {{-- Контент вкладок вынесен в отдельные файлы для соблюдения структуры --}}
            @if($tab === 'info')
                @include('partials._entity-info-tab', ['entity' => $clinic])
            @endif

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
            @if($clinic->photos->isNotEmpty())
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    @include('partials._gallery-cover', [
                        'entity' => $clinic,
                        'fallbackUrl' => $logo,
                        'alt' => $clinic->name,
                    ])
                </div>
            </div>
            @endif

            <div class="mt-3">
                @include('partials._working-hours-card', ['entity' => $clinic])
            </div>
        </div>
    </div>

    {{-- СПИСОК ДОКТОРОВ (нижняя секция) --}}
    <div class="mb-4 mt-5">
        <h2 class="fs-5 fw-semibold mb-3">Доктора клиники</h2>
        @php
            // Врач может работать в нескольких клиниках (сводная таблица clinic_doctor) —
            // раньше тут была выборка только по «основной» clinic_id, и врач пропадал
            // из карточек остальных мест работы.
            $doctors = $clinic->doctors()->orderBy('name')->get();
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

    {{-- СПИСОК СПЕЦИАЛИСТОВ (специалисты тоже могут работать в клинике; блок показываем, только если они есть) --}}
    @php
        $clinicSpecialists = $clinic->specialists()->withAvg('reviews', 'rating')->orderBy('name')->get();
    @endphp
    @if ($clinicSpecialists->isNotEmpty())
    <div class="mb-4 mt-5">
        <h2 class="fs-5 fw-semibold mb-3">Специалисты клиники</h2>

        <div class="row g-3">
            @foreach ($clinicSpecialists as $specialist)
                @php
                    $specialistAvgRating = $specialist->reviews_avg_rating ? number_format($specialist->reviews_avg_rating, 1) : '0.0';
                @endphp
                <div class="col-md-6 col-lg-4 col-sm-6">
                    <a href="{{ route('specialists.show', $specialist->slug) }}" class="text-decoration-none text-reset">
                        <div class="card h-100 shadow-sm border-0 position-relative doctor-card">
                            <div class="rating-badge">
                                <img width="24px" src="{{ asset('storage/icon/stars/doctors_stars.png') }}" alt="Рейтинг">
                                <span class="rating-value">{{ $specialistAvgRating }}</span>
                            </div>
                            <div class="card-body text-center">
                                <img src="{{ $specialist->photo ? asset('/storage/' . $specialist->photo) : asset('/storage/specialists/default-specialist.webp') }}"
                                     alt="{{ $specialist->name }}" class="doctor-photo mb-3">
                                <h5 class="card-title mb-1">{{ $specialist->name }}</h5>
                                <p class="text-muted mb-2">{{ $specialist->specialization ?? 'Специалист' }}</p>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
    @endif

</main>



<footer class="footer-fullwidth mt-auto w-100">
    @include('layouts.footer')
</footer>
@endsection