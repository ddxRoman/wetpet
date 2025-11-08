@extends('layouts.clinics_catalog')

@section('title', 'Каталог ветеринарных клиник')

@section('content')
<div class="container py-5">
    <h1 class="mb-4 text-center">Каталог ветеринарных клиник
        @if(!empty($selectedCity))
            <small class="text-muted d-block fs-6">Город: {{ $selectedCity }}</small>
        @endif
    </h1>
    <div class="row g-4">
        @foreach ($clinics as $clinic)
            @php
                // Подсчёт средней оценки и количества отзывов
                $avgRating = $clinic->reviews->avg('rating') ?? 0;
                $reviewCount = $clinic->reviews->count();
                $ratingCounts = $clinic->reviews->groupBy('rating')->map->count();
            @endphp

            <div class="col-lg-3 col-md-4 col-12">
                <a href="{{ route('clinics.show', $clinic->id) }}" class="text-decoration-none text-reset">
                    <div class="card h-100 shadow-sm hover-shadow position-relative transition">

                        {{-- ⭐ Средняя оценка --}}
@php
    $sortedRatings = $ratingCounts->sortKeysDesc();
    $tooltipHtml = '<div style="min-width:160px;">';
    $tooltipHtml .= '<strong>Всего отзывов:</strong> ' . $reviewCount . '<br><hr class="my-1">';
    foreach ($sortedRatings as $rating => $count) {
        $percent = $reviewCount > 0 ? round(($count / $reviewCount) * 100) : 0;
        $tooltipHtml .= "
            <div style='font-size:0.85rem;'>
                ⭐ {$rating}
                <div style='background:#eee; height:6px; border-radius:4px; overflow:hidden; margin-top:2px;'>
                    <div style='width:{$percent}%; background:#ffc107; height:100%;'></div>
                </div>
                <small>{$count} отзывов</small>
            </div>
        ";
    }
    $tooltipHtml .= '</div>';
@endphp


<div class="rating-badge position-absolute top-0 start-0 m-2 px-2 py-1 bg-warning text-dark rounded-pill d-flex align-items-center"
     data-bs-toggle="tooltip"
     data-bs-html="true"
     title="
        Всего отзывов: {{ $reviewCount }}
        @for ($rating = 5; $rating >= 1; $rating--)
             {{ str_repeat('⭐', $rating) }} — {{ $ratingCounts[$rating] ?? 0 }} оцен{{ ($ratingCounts[$rating] ?? 0) == 1 ? 'ка' : ((($ratingCounts[$rating] ?? 0) >= 2 && ($ratingCounts[$rating] ?? 0) <= 4) ? 'ки' : 'ок') }}
        @endfor
     ">
⭐ <span class="ms-1 fw-semibold">{{ number_format($avgRating, 1) }}</span>
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
    </div>
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
</style>

{{-- Инициализация tooltip --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));
});
</script>
@endsection
