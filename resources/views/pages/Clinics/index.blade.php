@extends('layouts.clinics_catalog')

@section('title', 'Каталог ветеринарных клиник')

@section('content')
<div class="container py-5">
    <h1 class="mb-4 text-center">Каталог ветеринарных клиник
        @if(!empty($selectedCity))
        <small class="text-muted d-block fs-6">Город: {{ $selectedCity }}</small>
        @endif
    </h1>

    {{-- Если город не выбран — не показываем все клиники, а просим выбрать город --}}
    @if(empty($selectedCity))
    <div class="alert alert-info text-center">
        Пожалуйста, выберите город и обновите страницу — список клиник будет отображён только для выбранного города.
    </div>
    @else
    <div class="row g-4">
        @php
        $filtered = $clinics->filter(function($clinic) use ($selectedCity) {
        return isset($clinic->city) && (trim($clinic->city) === trim($selectedCity));
        })->values();
        @endphp

        @if($filtered->isEmpty())
        <div class="col-12">
            <div class="alert alert-warning text-center">
                Для города <strong>{{ $selectedCity }}</strong> клиник пока не найдено. <br>
<button class="btn_add_clinic btn-sm"
        data-bs-toggle="modal"
        data-bs-target="#addOrganizationModal"
        data-city="{{ session('city_name') }}"
        data-region="{{ session('region_name') }}">
    <img class="add_btn"
         src="{{ Storage::url('icon/button/add_clinic_btn.png') }}"
         alt="Добавить организацию">
    Добавить клинику
</button>

            </div>
        </div>
        @else
        @foreach ($filtered as $clinic)
        @php
        $reviewsCollection = $clinic->reviews ?? collect();
        $avgRating = $reviewsCollection->avg('rating') ? number_format($reviewsCollection->avg('rating'), 1) : '0.0';
        $reviewCount = $reviewsCollection->count();
        $ratingCounts = $reviewsCollection->groupBy('rating')->map->count();
        @endphp

        <div class="col-lg-3 col-md-4 col-12">
            <a href="{{ route('clinics.show', $clinic->slug) }}" title="Перейти в карточку клиники" class="text-decoration-none text-reset">
                <div class="card h-100 shadow-sm hover-shadow position-relative transition">
                    {{-- Rating badge --}}
                    <div class="rating-badge position-absolute top-0 start-0 m-2 px-2 py-1 bg-warning text-dark rounded-pill d-flex align-items-center"
                        data-bs-toggle="tooltip"
                        data-bs-html="true"
                        title="
                                        Всего отзывов: {{ $reviewCount }}
                                        @for ($rating = 5; $rating >= 1; $rating--)
                                             {{ str_repeat('⭐', $rating) }} — {{ $ratingCounts[$rating] ?? 0 }}
                                        @endfor
                                     ">
                        ⭐ <span class="ms-1 fw-semibold">{{ $avgRating }}</span>
                    </div>

                    @php
                    $logo = !empty($clinic->logo)
                    ? asset('storage/' . $clinic->logo)
                    : asset('storage/clinics/logo/default.webp');
                    @endphp

                    <img src="{{ $logo }}" class="card-img-top object-fit-contain p-3" alt="{{ $clinic->name }}">

                    <div class="card-body">
                        <h5 class="card-title">{{ $clinic->name }}</h5>
                        <p class="card-text mb-2">
                            {{ $clinic->country }}, {{ $clinic->city }}, {{ $clinic->street }} {{ $clinic->house }}
                        </p>
                        @if(!empty($clinic->schedule))
                        <p class="text-muted mb-0">
                            График: {{ $clinic->schedule }}
                        </p>
                        @endif
                    </div>
                </div>
            </a>
        </div>
        @endforeach
        @endif
    </div>
    @endif
</div>


{{-- Инициализация tooltip --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));
    });
</script>
@endsection