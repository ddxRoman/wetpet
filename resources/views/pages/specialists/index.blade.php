@extends('layouts.catalog')

@section('content')
<div class="container py-5">
    <h1 class="mb-4 text-center">Каталог ветеринарных врачей
        @if(!empty($selectedCity))
            <small class="text-muted d-block fs-6"> {{ $selectedCity }}</small>
        @endif
    </h1>

    {{-- Если город не выбран — не показываем всех врачей --}}
    @if(empty($selectedCity))
        <div class="alert alert-info text-center">
            Пожалуйста, выберите город — список врачей будет отображён только для выбранного города.
        </div>
    @else

        {{-- БЛОК ТЕГОВ (Фильтр по специализациям) --}}
        <div class="specialization-filter-wrapper mb-4">
            <div class="d-inline-flex gap-2 specialization-filter pb-2">
                {{-- Ссылка "Все" --}}
                <a href="{{ route('doctors.index', ['city_id' => $currentCityId ?? request('city_id')]) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ empty($selectedSpecialization) ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Все
                </a>

                @foreach($specializations as $spec)
                    @if(!empty($spec))
                        <a href="{{ route('doctors.index', [
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

        <style>
            /* Стили для горизонтального скролла тегов на мобилках */
            .specialization-filter-wrapper {
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }
            .specialization-filter::-webkit-scrollbar {
                display: none; /* Скрываем скроллбар */
            }
            .specialization-filter {
                -ms-overflow-style: none;
                scrollbar-width: none;
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

        @if($doctors->isEmpty())
            <div class="alert alert-warning text-center">
                Ветеринарные врачи в городе <strong>{{ $selectedCity }}</strong> не найдены. <br>
                <button class="btn_add_clinic btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#addDoctorModal">
                    <img class="add_btn" src="{{ Storage::url('icon/button/add_doctor_btn.png') }}" alt="Добавить ветеринара">
                    Добавить ветеринара
                </button>
            </div>
        @else

        <div class="doctors-list">
            <div class="row g-4" id="clinics-grid"> {{-- Добавил ID для работы твоего JS пагинации --}}

                @foreach ($doctors as $doctor)
                    @php
                        $reviewsCollection = $doctor->reviews ?? collect();
                        $avgRating = $doctor->reviews_avg_rating ? number_format($doctor->reviews_avg_rating, 1) : '0.0';
                        $reviewCount = $reviewsCollection->count();
                        $ratingCounts = $reviewsCollection->groupBy('rating')->map->count();
                    @endphp

                    <div class="col-lg-3 col-md-4 col-12 doctor-item"> {{-- Класс для JS --}}
                        <a href="{{ route('doctors.show', $doctor->slug) }}" title="Открыть карточку доктора" class="text-decoration-none text-reset">
                            <div class="card h-100 shadow-sm hover-shadow position-relative transition">

                                {{-- ⭐ Рейтинг --}}
                                <div class="rating-badge position-absolute top-0 start-0 m-2 px-2 py-1 bg-warning text-dark rounded-pill d-flex align-items-center"
                                     data-bs-toggle="tooltip"
                                     data-bs-html="true"
                                     title="Всего отзывов: {{ $reviewCount }}">
                                    ⭐ <span class="ms-1 fw-semibold">{{ $avgRating }}</span>
                                </div>

                                {{-- 🦎 Экзотические животные --}}
                                @if($doctor->exotic_animals == 'Да' || $doctor->exotic_animals == 1)
                                    <div class="exotic-icon position-absolute top-0 end-0 m-2 bg-white rounded-circle shadow d-flex align-items-center justify-content-center"
                                         style="width:34px;height:34px;font-size:18px; z-index: 20;">
                                        <img src="{{ asset('storage/icon/stars/exotic.png') }}"
                                            alt="Экзотические животные"
                                            title="Работает с экзотами"
                                            style="width:32px; height:32px; border-radius: 25px;">
                                    </div>
                                @endif

                                @php
                                    $photo = !empty($doctor->photo)
                                        ? asset('storage/' . $doctor->photo)
                                        : asset('storage/doctors/default-doctor.webp');
                                @endphp

                                <img src="{{ $photo }}" class="card-img-top object-fit-contain p-3" alt="{{ $doctor->name }}">

                                <div class="card-body">
                                    <h5 class="card-title">{{ $doctor->name }}</h5>
                                    <p class="card-text mb-1 mt-2">
                                        <strong>Специализация:</strong> {{ $doctor->specialization }}
                                    </p>
                                    @if(!empty($doctor->city))
                                        <p class="card-text mb-1">
                                            <strong>Город:</strong> {{ $doctor->city->name }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        @if($doctors->hasMorePages())
            <div class="text-center mt-5 mb-5" id="load-more-container">
                <button id="load-more" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm" 
                        data-url="{{ $doctors->nextPageUrl() }}">
                    Показать еще
                </button>
            </div>
        @endif

        @endif {{-- end empty block --}}
    @endif {{-- end city check --}}
</div>

@section('modals')
    @include('account.modals.modal-add-specialist', ['cities' => $cities ?? []])
@endsection
@endsection