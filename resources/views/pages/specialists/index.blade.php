@extends('layouts.catalog')

@section('content')
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
                   class="org-filter-pill {{ empty($selectedSpecialization) ? 'org-filter-pill--active' : '' }}">
                    Все <span class="org-filter-pill__count">{{ $totalSpecialistsCount }}</span>
                </a>

                @foreach($specializations as $spec)
                    @if(!empty($spec))
                        <a href="{{ route('specialists.index', [
                                'specialization' => $spec, 
                                'city_id' => $currentCityId ?? request('city_id')
                            ]) }}" 
                           class="org-filter-pill {{ $selectedSpecialization == $spec ? 'org-filter-pill--active' : '' }}">
                            {{ $spec }} <span class="org-filter-pill__count">{{ $specializationCounts[$spec] ?? 0 }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        @if($specialists->isEmpty())
            <div class="alert alert-warning text-center">
                Ветеринарные специалисты в городе <strong>{{ $selectedCity }}</strong> не найдены. <br>
                <button class="btn_add_clinic btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#addDoctorModal">
                    <img class="add_btn" src="{{ Storage::url('icon/button/add_doctor_btn.png') }}" alt="Добавить ветеринара">
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
                            <div class="card catalog-card h-100 shadow-sm hover-shadow position-relative transition">
                                
                                {{-- ⭐ Рейтинг --}}
                                <div class="rating-badge position-absolute top-0 start-0 m-2 px-2 py-1 bg-warning text-dark rounded-pill d-flex align-items-center"
                                     data-bs-toggle="tooltip"
                                     data-bs-html="true"
                                     title="Всего отзывов: {{ $reviewCount }}">
                                    ⭐ <span class="ms-1 fw-semibold">{{ $avgRating }}</span>
                                </div>

                                @php
                                    $photo = !empty($specialist->photo) ? asset('storage/' . $specialist->photo) : asset('storage/specialists/default-specialist.webp');
                                @endphp

                                <img src="{{ $photo }}" class="card-img-top object-fit-contain p-3" alt="{{ $specialist->name }}">

                                <div class="card-body">
                                    <h5 class="org-card-title">{{ $specialist->name }}</h5>
                                    <span class="org-type-badge">{{ $specialist->specialization }}</span>
                                    @if(!empty($specialist->city))
                                        <p class="org-address">
                                            <b class="">Город: </b> {{ $specialist->city->name }}
                                        </p>
                                    @endif
                                    @include('partials._promotions-badge', ['entity' => $specialist])
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
    // Tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));

    // Кнопка "Показать ещё"
    const grid = document.getElementById('clinics-grid');

    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('#load-more');
        if (!btn) return;

        const url = btn.dataset.url;
        if (!url) return;

        btn.disabled = true;
        btn.textContent = 'Загрузка...';

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await response.text();

            // Парсим HTML и достаём новые карточки
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newCards = doc.querySelectorAll('#clinics-grid .specialist-item');

            newCards.forEach(card => {
                grid.appendChild(card);
                // Инициализируем тултипы уже в живом DOM
                card.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
            });

            // Обновляем URL следующей страницы
            const nextBtn = doc.getElementById('load-more');
            if (nextBtn && nextBtn.dataset.url) {
                btn.dataset.url = nextBtn.dataset.url;
                btn.disabled = false;
                btn.textContent = 'Показать еще';
            } else {
                // Страниц больше нет — убираем кнопку
                btn.closest('.text-center')?.remove();
            }
        } catch (err) {
            btn.disabled = false;
            btn.textContent = 'Показать еще';
            console.error('Ошибка загрузки:', err);
        }
    });
});
</script>


@endsection