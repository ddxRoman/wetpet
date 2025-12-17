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
                // безопасная фильтрация коллекции клиник на стороне представления:
                // если контроллер уже отфильтровал — это вернёт всё то же самое,
                // если нет — мы убережёмся и покажем только нужные.
                $filtered = $clinics->filter(function($clinic) use ($selectedCity) {
                    // в базе поле city может быть либо id, либо название — сравним по названию
                    return isset($clinic->city) && (trim($clinic->city) === trim($selectedCity));
                })->values();
            @endphp

            @if($filtered->isEmpty())
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        Для города <strong>{{ $selectedCity }}</strong> клиник пока не найдено. <br>
                                        <button class="btn_add_clinic btn-sm" data-bs-toggle="modal" data-bs-target="#addOrganizationModal">
                    <img class="add_btn"  src="{{ Storage::url('icon/button/add_clinic_btn.png') }}" title="Добавить организацию" alt="Добавить организацию">
                                        Добавить клинику
                </button>
                    </div>
                </div>
            @else
                @foreach ($filtered as $clinic)
                    @php
                        // Подсчёт средней оценки и количества отзывов (защитим от null)
                        $reviewsCollection = $clinic->reviews ?? collect();
                        $avgRating = $reviewsCollection->avg('rating') ? number_format($reviewsCollection->avg('rating'), 1) : '0.0';
                        $reviewCount = $reviewsCollection->count();
                        $ratingCounts = $reviewsCollection->groupBy('rating')->map->count();
                    @endphp

                    <div class="col-lg-3 col-md-4 col-12">
                        <a href="{{ route('clinics.show', $clinic->id) }}" class="text-decoration-none text-reset">
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

<style>
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: none;
    border-radius: 12px;
    overflow: hidden;
}
.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
}
.card-img-top {
    height: 160px;
    object-fit: contain;
    background-color: #f8f9fa;
    border-bottom: 1px solid #eee;
}

/* Значок рейтинга */
.rating-badge {
    font-size: 0.9rem;
    line-height: 1;
    background-color: #FFD700 !important;
    color: #222 !important;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
    z-index: 2;
}

/* Подсказки Bootstrap */
.tooltip-inner {
    background-color: #fff !important;
    color: #222 !important;
    border: 1px solid #ddd;
    font-size: 0.85rem;
    text-align: left;
}
.tooltip.bs-tooltip-auto[data-popper-placement^=top] .tooltip-arrow::before {
    border-top-color: #ddd !important;
}
.btn_add_clinic{
    border: 1px solid #222;
    background-color: #002fff21;
    margin-top: 2%;
    margin-bottom: 2%;
    padding: 0.2%;
    border-radius: 5px;
}
</style>

{{-- Инициализация tooltip --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));
});
</script>
@endsection
