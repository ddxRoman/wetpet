<!-- Карточка одной отдельной записи (Организация) -->

@extends('layouts.app')

@section('content')
@php
    if (!isset($organization)) {
        abort(404);
    }

    $photo = $organization->logo && file_exists(public_path('storage/'.$organization->logo))
        ? asset('storage/'.$organization->logo)
        : asset('storage/organizations/default-organization.webp');

    $addressParts = array_filter([
        $organization->city ?? '',
        $organization->street ?? '',
        $organization->house ?? '',
    ]);

    $mapQuery = urlencode(implode(', ', $addressParts));

    $tab = request('tab', 'info');
@endphp
    @include('layouts.header')
<main class="flex-grow-1 container mt-5">

    {{-- КНОПКА НАЗАД --}}
    <div class="mb-4">

                        <div class="mb-3">
                <a href="{{ route('organizations.index') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 shadow-sm back-to-catalog"
           title="Вернутся к каталогу всех организаций города">
                    <img src="{{ asset('storage/icon/button/arrow-back.svg') }}" width="22" alt="paw">
                    В каталог
                </a>
            </div>
    </div>

{{-- ШАПКА --}}
    <div class="d-flex align-items-start justify-content-between flex-wrap mb-4">

        {{-- Левый блок: Фото + Инфо --}}
        <div class="d-flex align-items-start flex-wrap flex-grow-1">
            <img src="{{ $photo }}"
                 style="width:90px;height:90px;border-radius:10px;object-fit:contain;background:#f8f9fa"
                 class="me-3 border p-1">

            <div class="flex-grow-1">
                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                    <h1 class="fw-bold m-0" style="font-size: 1.75rem;">{{ $organization->name }}</h1>

                    {{-- ⭐ Блок рейтинга --}}
                    @php
                        use App\Models\Review;
                        $organizationReviews = Review::where('reviewable_id', $organization->id)
                            ->where('reviewable_type', \App\Models\Organization::class)
                            ->get();
                        $reviewCount = $organizationReviews->count();
                        $averageRating = $reviewCount > 0 ? round($organizationReviews->avg('rating'), 1) : null;
                    @endphp

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
                    {{ $organization->activityType->name ?? '' }}
                </div>

                <div class="text-muted small mt-1">
                    {{ implode(', ', $addressParts) }}
                </div>
            </div>
        </div>

        {{-- Правый блок: Кнопка "ЭТО Я" --}}
        <div class="ms-md-3 mt-3 mt-md-0">
            @auth
                @php
                    $ownerPivot = $organization->owners()
                        ->where('user_id', auth()->id())
                        ->first();
                    $alreadyOwner = $ownerPivot ? $ownerPivot->pivot : null;
                @endphp

                @if($alreadyOwner && $alreadyOwner->is_confirmed)
                    <span class="btn btn-success fw-bold disabled d-flex align-items-center gap-2"
                          style="border-radius: 10px; padding: 8px 16px; opacity: .7;">
                        ✓ Это Ваша организация
                    </span>
                @elseif($alreadyOwner && !$alreadyOwner->is_confirmed)
                    <button class="btn btn-warning fw-bold d-flex align-items-center gap-2"
                            style="border-radius: 10px; padding: 8px 16px;"
                            data-bs-toggle="modal" data-bs-target="#claimOwnershipModal">
                        ⏳ На проверке (дополнить)
                    </button>
                @else
                    <button class="btn btn-success fw-bold d-flex align-items-center gap-2"
                            style="border-radius: 10px; padding: 8px 16px; border-style: dashed;"
                            data-bs-toggle="modal" data-bs-target="#claimOwnershipModal">
                        <i class="bi bi-person-check"></i>
                        Это я
                    </button>
                @endif

                @include('partials.modal-claim-ownership', ['entityType' => 'organization', 'entityId' => $organization->id])
            @else
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}"
                   class="btn btn-success fw-bold d-flex align-items-center gap-2"
                   style="border-radius: 10px; padding: 8px 16px; border-style: dashed;">
                    Это я
                </a>
            @endauth
        </div>
    </div>

    {{-- АКЦИИ --}}
    @include('partials._promotions-widget', ['entity' => $organization])

    {{-- ТАБЫ --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'info' ? 'active' : '' }}"  title="Просмотреть общую информацию" href="?tab=info">Информация</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'contacts' ? 'active' : ''  }}" title="Просмотреть контакты" href="?tab=contacts">Контакты</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'services' ? 'active' : ''  }}" title="Посмотреть перечень услуг, которые оказывает данная организация" href="?tab=services">Услуги</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'reviews' ? 'active' : ''  }}" title="Прочитать отзывы" href="?tab=reviews">Отзывы</a>
        </li>
    </ul>

    <div class="row">
        <div class="col-lg-8">
            @if($tab === 'info')
                @include('pages.organizations.tabs.info', ['organization' => $organization])
            @endif

            @if($tab === 'contacts')
                @include('pages.organizations.tabs.contacts', ['organization' => $organization])
            @endif

            @if($tab === 'services')
                @include('pages.organizations.tabs.services', ['organization' => $organization])
            @endif

            @if($tab === 'reviews')
                @include('pages.organizations.tabs.reviews', ['organization_id' => $organization->id])
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <img src="{{ $photo }}"
                         style="width:100%;max-width:280px;border-radius:10px;object-fit:contain">
                </div>
            </div>

            <div class="mt-3">
                @include('partials._working-hours-card', ['entity' => $organization])
            </div>
        </div>
    </div>

    {{-- СПИСОК СПЕЦИАЛИСТОВ (нижняя секция) --}}
    <div class="mb-4 mt-5">
        <h2 class="fs-5 fw-semibold mb-3">Специалисты организации</h2>
        @php
            $specialists = \App\Models\Specialist::where('organization_id', $organization->id)->withAvg('reviews', 'rating')->orderBy('name')->get();
        @endphp

        <div class="row g-3">
            @forelse ($specialists as $specialist)
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
            @empty
                <p class="text-muted">Специалисты этой организации ещё не указаны.</p>
            @endforelse
        </div>
    </div>
</main>
<footer class="footer-fullwidth mt-auto w-100">
    @include('layouts.footer')
</footer>
@endsection