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
<div class="specialization-filter-wrapper mb-4">
    <div class="d-inline-flex gap-2 specialization-filter pb-2">
        <div class="specialization-filter">
            {{-- Ссылка "Все" --}}
            <a href="{{ route('doctors.index', ['city_id' => request('city_id')]) }}" 
               class="org-filter-pill {{ empty($selectedSpecialization) ? 'org-filter-pill--active' : '' }}">
                Все <span class="org-filter-pill__count">{{ $totalDoctorsCount }}</span>
            </a>

            @foreach($specializations as $spec)

                    <a href="{{ route('doctors.index', ['specialization' => $spec, 'city_id' => request('city_id')]) }}" 
                       class="org-filter-pill {{ $selectedSpecialization == $spec ? 'org-filter-pill--active' : '' }}">
                        {{ $spec }} <span class="org-filter-pill__count">{{ $specializationCounts[$spec] ?? 0 }}</span>
                    </a>
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
                            <div class="card catalog-card h-100 shadow-sm hover-shadow position-relative transition">

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
                                    <h5 class="org-card-title">{{ $doctor->name }}</h5>

                                    @if($doctor->exotic_animals == 'Да' || $doctor->exotic_animals == 1)
                                        <span class="org-type-badge">Экзотические животные</span>
                                    @endif

                                    <span class="org-type-badge">{{ $doctor->specialization }}</span>

                                    @if(!empty($doctor->city))
                                        <p class="org-address mt-2">
                                            <i class="bi bi-geo-alt-fill"></i> {{ $doctor->city->name }}
                                        </p>
                                    @endif

                                    @if(!empty($doctor->experience_label))
                                        <p class="org-hours">
                                            Стаж: {{ $doctor->experience_label }}
                                        </p>
                                    @endif
                                    @include('partials._promotions-badge', ['entity' => $doctor])
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