@extends('layouts.catalog')

@section('content')

        <style>
            /* Стили для горизонтального скролла тегов */
            .specialization-filter-wrapper {
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 5px;
            }
            .specialization-filter-wrapper::-webkit-scrollbar {
                height: 4px;
            }
            .specialization-filter-wrapper::-webkit-scrollbar-thumb {
                background: #dee2e6;
                border-radius: 10px;
            }
            .btn-outline-secondary {
                background-color: #f8f9fa;
                border-color: #dee2e6;
                color: #333;
            }
            .btn-outline-secondary:hover {
                background-color: #e9ecef;
                color: #000;
                border-color: #adb5bd;
            }
        </style>

<div class="container py-5">
    <h1 class="mb-4 text-center">Каталог ветеринарных специалистов
        @if(!empty($selectedCity))
            <small class="text-muted d-block fs-6"> {{ $selectedCity }}</small>
        @endif
    </h1>

    @if(empty($selectedCity))
        <div class="alert alert-info text-center">
            Пожалуйста, выберите город — список специалистов будет отображён только для выбранного города.
        </div>
    @else

        {{-- БЛОК ТЕГОВ --}}
        <div class="specialization-filter-wrapper mb-4">
            <div class="d-inline-flex gap-2 specialization-filter pb-2">
                {{-- Ссылка "Все" теперь ведет на specialists.index --}}
                <a href="{{ route('specialists.index', ['city_id' => $currentCityId ?? request('city_id')]) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ empty($selectedSpecialization) ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Все
                </a>

                @foreach($specializations as $spec)
                    @if(!empty($spec))
                        <a href="{{ route('specialists.index', [
                                'specialization' => $spec, 
                                'city_id' => $currentCityId ?? request('city_id')
                            ]) }}" 
                           class="btn btn-sm rounded-pill px-3 {{ $selectedSpecialization == $spec ? 'btn-primary' : 'btn-outline-secondary' }}">
                            {{ $spec }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        @if($specialists->isEmpty())
            <div class="alert alert-warning text-center">
                Ветеринарные специалисты в городе <strong>{{ $selectedCity }}</strong> не найдены. <br>
                <button class="btn_add_clinic btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#addSpecialistModal">
                    Добавить специалиста
                </button>
            </div>
        @else

        <div class="specialists-list">
            <div class="row g-4" id="clinics-grid">
                @foreach ($specialists as $specialist)
                    @php
                        $avgRating = $specialist->reviews_avg_rating ? number_format($specialist->reviews_avg_rating, 1) : '0.0';
                        $reviewCount = $specialist->reviews_count ?? ($specialist->reviews ? $specialist->reviews->count() : 0);
                    @endphp

                    <div class="col-lg-3 col-md-4 col-12 specialist-item">
                        {{-- Ссылка ведет на specialists.show --}}
                        <a href="{{ route('specialists.show', $specialist->slug) }}" class="text-decoration-none text-reset">
                            <div class="card h-100 shadow-sm hover-shadow position-relative transition">
                                
                                {{-- ⭐ Рейтинг --}}
                                <div class="rating-badge position-absolute top-0 start-0 m-2 px-2 py-1 bg-warning text-dark rounded-pill d-flex align-items-center"
                                     data-bs-toggle="tooltip"
                                     data-bs-html="true"
                                     title="Всего отзывов: {{ $reviewCount }}">
                                    ⭐ <span class="ms-1 fw-semibold">{{ $avgRating }}</span>
                                </div>

                                @php
                                    $photo = !empty($specialist->photo) ? asset('storage/' . $specialist->photo) : asset('storage/doctors/default-doctor.webp');
                                @endphp

                                <img src="{{ $photo }}" class="card-img-top object-fit-contain p-3" alt="{{ $specialist->name }}">

                                <div class="card-body">
                                    <h5 class="card-title">{{ $specialist->name }}</h5>
                                    <p class="card-text mb-1 mt-2">
                                        <strong>Специализация:</strong> {{ $specialist->specialization }}
                                    </p>
                                    @if(!empty($specialist->city))
                                        <p class="card-text mb-1">
                                            <strong>Город:</strong> {{ $specialist->city->name }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        @if($specialists->hasMorePages())
            <div class="text-center mt-5 mb-5">
                <button id="load-more" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm" data-url="{{ $specialists->nextPageUrl() }}">
                    Показать еще
                </button>
            </div>
        @endif

        @endif 
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));
});
</script>


@endsection

