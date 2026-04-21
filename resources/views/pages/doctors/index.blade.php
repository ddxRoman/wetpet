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
                        $reviewsCollection = $doctor->reviews ?? collect();
                        // Используем системное поле reviews_avg_rating из контроллера
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