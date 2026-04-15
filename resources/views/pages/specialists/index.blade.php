@extends('layouts.clinics_catalog')
@section('content')
<div class="container header-specialist py-2">
    <h1 class="text-center">Специалисты
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
<div class="specialization-filter mb-4 py-2" style="overflow-x: auto; text-align:center; white-space: nowrap; -webkit-overflow-scrolling: touch;">
    <div class="d-inline-flex gap-2">
        {{-- Кнопка "Все" --}}
        <a href="{{ route('specialists.index') }}" 
           class="btn btn-sm rounded-pill px-3 {{ empty($selectedSpecialization) ? 'btn-primary' : 'btn-outline-secondary' }}"
           style="transition: all 0.3s;">
            Все
        </a>

@foreach($specializations as $spec)
    @if(!empty($spec))
        <a href="{{ route('specialists.index', ['specialization' => $spec, 'city_id' => request('city_id')]) }}" 
           class="btn btn-sm rounded-pill px-3 {{ $selectedSpecialization == $spec ? 'btn-primary' : 'btn-outline-secondary' }}">
            {{ $spec }}
        </a>
    @endif
@endforeach
    </div>
</div>

<style>
    /* Стили для скрытия скроллбара, но сохранения прокрутки */
    .specialization-filter::-webkit-scrollbar {
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
    .header-search{
margin-bottom: 2% !important;
    }
</style>

        {{-- Если нет врачей для выбранного города (теперь используем $doctors напрямую) --}}
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
    // Временно создаем пустую коллекцию, пока не настроены отзывы для врачей
    $reviewsCollection = collect(); 
    $avgRating = '0.0';
    $reviewCount = 0;
    $ratingCounts = collect();
@endphp

                    <div class="col-lg-3 col-md-4 col-12">
                        <a href="{{ route('specialists.show', $doctor->slug) }}" title="Открыть карточку доктора" class="text-decoration-none text-reset">
                            <div class="card h-100 shadow-sm hover-shadow position-relative transition">

                                {{-- ⭐ Рейтинг --}}
                                <div class="rating-badge position-absolute top-0 start-0 m-2 px-2 py-1 bg-warning text-dark rounded-pill d-flex align-items-center"
                                     data-bs-toggle="tooltip"
                                     data-bs-html="true"
                                     title="
                                        Всего отзывов: {{ $reviewCount }}
                                        @for ($r = 5; $r >= 1; $r--)
                                            ⭐ {{ $r }} — {{ $ratingCounts[$r] ?? 0 }} отзыв{{ ($ratingCounts[$r] ?? 0) == 1 ? '' : 'ов' }}
                                        @endfor
                                     ">
                                    ⭐ <span class="ms-1 fw-semibold">{{ $avgRating }}</span>
                                </div>

                                {{-- 🦎 Экзотические животные --}}
                                @if($doctor->exotic_animals == 'Да' || $doctor->exotic_animals == 1)
                                    <div class="exotic-icon position-absolute top-0 end-0 m-2 bg-white rounded-circle shadow d-flex align-items-center justify-content-center"
                                         style="width:34px;height:34px;font-size:18px; z-index: 20;">
                                        <img src="{{ asset('storage/icon/stars/exotic.png') }}"
                                            alt="Экзотические животные"
                                            title="Данный специалист работает с экзотическими животными, рептилиями, амфибиями, птицами, грызунами, зайцеобразными и мелкими млекопитающими"
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
                                        <span class="badge bg-warning text-dark" style="font-size: 0.8rem;">
                                            Экзотические животные
                                        </span>
                                    @endif

                                    <p class="card-text mb-1 mt-2">
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

        @endif {{-- end empty block --}}

    @endif {{-- end city check --}}
</div>

@section('modals')
    @include('account.modals.modal-add-specialist', ['cities' => $cities ?? []])
@endsection

{{-- Tooltip init --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));
});
</script>
@endsection