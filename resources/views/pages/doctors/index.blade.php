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

        {{-- Фильтр по специализациям (Теги) --}}
<div class="specialization-filter-container mb-4">
    <div class="scroll-wrapper">
        <div class="specialization-filter">
            {{-- Ссылка "Все" --}}
            <a href="{{ route('doctors.index', ['city_id' => request('city_id')]) }}" 
               class="btn btn-sm rounded-pill px-3 {{ empty($selectedSpecialization) ? 'btn-primary' : 'btn-outline-secondary' }}">
                Все
            </a>

            @foreach($specializations as $spec)
                @if(!empty($spec))
                    <a href="{{ route('doctors.index', ['specialization' => $spec, 'city_id' => request('city_id')]) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ $selectedSpecialization == $spec ? 'btn-primary' : 'btn-outline-secondary' }}">
                        {{ $spec }}
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</div>

        {{-- Если нет врачей для выбранного города --}}
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
            <div class="row g-4">
                @foreach ($doctors as $doctor)
                    @php
                        $reviewsCollection = $doctor->reviews ?? collect();
                        $avgRating = $doctor->reviews_avg_rating ? number_format($doctor->reviews_avg_rating, 1) : '0.0';
                        $reviewCount = $reviewsCollection->count();
                        $ratingCounts = $reviewsCollection->groupBy('rating')->map->count();
                    @endphp

                    <div class="col-lg-3 col-md-4 col-12">
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
                                            title="Данный специалист работает с экзотическими животными"
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

                                    @if($doctor->exotic_animals == 'Да' || $doctor->exotic_animals == 1)
                                        <span class="badge bg-warning text-dark mb-2" style="font-size: 0.8rem;">
                                            Экзотические животные
                                        </span>
                                    @endif

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

        @if($doctors->hasMorePages())
            <div class="text-center mt-5 mb-5" id="load-more-container">
                <button id="load-more" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm" 
                        data-url="{{ $doctors->nextPageUrl() }}">
                    Показать еще
                </button>
            </div>
        @endif

        <div class="d-none">
            {{ $doctors->links() }}
        </div>

        @endif 
    @endif 
</div>

<style>
    /* Горизонтальный скролл для фильтров на мобильных устройствах */
    .specialization-filter-container {
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 5px;
        -webkit-overflow-scrolling: touch;
    }
    .specialization-filter-container::-webkit-scrollbar {
        display: none;
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
    /* Контейнер для управления отступами */
    .specialization-filter-container {
        width: 100%;
        margin-bottom: 1.5rem;
    }

    /* Основной блок с прокруткой */
    .specialization-filter {
        display: flex;
        flex-wrap: nowrap; /* Запрещаем перенос строк */
        overflow-x: auto;  /* Включаем горизонтальную прокрутку */
        padding-bottom: 12px; /* Место для ползунка, чтобы он не перекрывал кнопки */
        gap: 8px;
        -webkit-overflow-scrolling: touch; /* Плавная прокрутка на iOS */
    }

    /* Стилизация полосы прокрутки (Scrollbar) */
    /* 1. Ширина/высота дорожки */
    .specialization-filter::-webkit-scrollbar {
        height: 6px; /* Высота ползунка */
    }

    /* 2. Фон дорожки */
    .specialization-filter::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    /* 3. Сам ползунок (бегунок) */
    .specialization-filter::-webkit-scrollbar-thumb {
        background: #ccc; /* Цвет ползунка */
        border-radius: 10px;
        transition: background 0.3s ease;
    }

    /* 4. Цвет при наведении */
    .specialization-filter::-webkit-scrollbar-thumb:hover {
        background: #adb5bd; 
    }

    /* Кнопки-теги (чтобы текст не переносился внутри кнопки) */
    .specialization-filter .btn {
        flex: 0 0 auto; /* Не дает кнопкам сжиматься */
        white-space: nowrap;
    }

    /* Для Firefox (у него свои свойства для скролла) */
    .specialization-filter {
        scrollbar-width: thin;
        scrollbar-color: #ccc #f1f1f1;
    }
</style>


@section('modals')
    @include('account.modals.modal-add-specialist', ['cities' => $cities ?? []])
@endsection

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));
});
</script>
@endsection