@extends('layouts.clinics_catalog')

@section('title', 'Каталог ветеринарных врачей')

@section('content')
<div class="container py-5">
    <h1 class="mb-4 text-center">Каталог ветеринарных врачей
        @if(!empty($selectedCity))
            <small class="text-muted d-block fs-6">Город: {{ $selectedCity }}</small>
        @endif
    </h1>

    <div class="doctors-list">
    <div class="row g-4">
        @foreach ($doctors as $doctor)
            @php
                // Подсчёт средней оценки и количества отзывов
                $avgRating = $doctor->reviews->avg('rating') ?? 0;
                $reviewCount = $doctor->reviews->count();
                $ratingCounts = $doctor->reviews->groupBy('rating')->map->count();
            @endphp

            <div class="col-lg-3 col-md-4 col-12">
                <a href="{{ route('doctors.show', $doctor->id) }}" class="text-decoration-none text-reset">
                    <div class="card h-100 shadow-sm hover-shadow position-relative transition">

                        {{-- ⭐ Блок со средним рейтингом --}}
                        @php
                            $sortedRatings = $ratingCounts->sortKeysDesc();
                        @endphp
                        <div class="rating-badge position-absolute top-0 start-0 m-2 px-2 py-1 bg-warning text-dark rounded-pill d-flex align-items-center"
                            data-bs-toggle="tooltip"
                            data-bs-html="true"
                            title="
                                Всего отзывов: {{ $reviewCount }}
                                @for ($rating = 5; $rating >= 1; $rating--)
                                     ⭐ {{ $rating }} — {{ $ratingCounts[$rating] ?? 0 }} отзыв{{ ($ratingCounts[$rating] ?? 0) == 1 ? ' ' : 'ов' }}
                                @endfor
                            ">
                        ⭐ <span class="ms-1 fw-semibold">{{ number_format($avgRating, 1) }}</span>
                        </div>

                        @php
                            $photo = !empty($doctor->photo)
                                ? asset('storage/' . $doctor->photo)
                                : asset('storage/default-doctor.png');
                        @endphp

                        <img src="{{ $photo }}" class="card-img-top object-fit-contain p-3" alt="{{ $doctor->name }}">

                        <div class="card-body">
                            <h5 class="card-title">{{ $doctor->name }}</h5>
                            <p class="card-text mb-1">
                                <strong>Специализация:</strong> {{ $doctor->specialization }}
                            </p>
                            @if(!empty($doctor->city))
                                <p class="card-text mb-1">
                                    <strong>Город:</strong> {{ $doctor->city->name }}
                                </p>
                            @endif
                            @if(!empty($doctor->experience))
                                <p class="text-muted mb-0">
                                    <strong>Стаж:</strong> {{ $doctor->experience }}
                                </p>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
    </div>
</div>


{{-- Инициализация tooltip --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));
});
</script>
@endsection
