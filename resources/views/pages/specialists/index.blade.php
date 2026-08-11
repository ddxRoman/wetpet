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

            /* Фильтр по специализации — фирменный стиль сайта */
            .org-filter-pill {
                display: inline-block;
                padding: 0.4rem 1rem;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 600;
                text-decoration: none;
                white-space: nowrap;
                background-color: #f4f9f4;
                border: 1px solid #dcefdc;
                color: #3d8b4c;
                transition: background-color 0.2s, border-color 0.2s, color 0.2s;
            }
            .org-filter-pill:hover {
                background-color: #e6f7e9;
                border-color: #b7e3bd;
                color: #2ecc71;
            }
            .org-filter-pill--active,
            .org-filter-pill--active:hover {
                background-color: #2ebfcc;
                border-color: #2e9ecc;
                color: #fff;
            }

            /* Карточка специалиста: визуальная иерархия текста */
            .org-card-title {
                font-size: 1.05rem;
                font-weight: 700;
                color: #212529;
                line-height: 1.3;
                margin-bottom: 0.4rem;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .org-type-badge {
                display: inline-block;
                font-size: 0.72rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.03em;
                color: #d95c1f;
                background-color: #fdece1;
                padding: 0.22rem 0.6rem;
                border-radius: 20px;
                margin-bottom: 0.6rem;
            }

            .org-address {
                font-size: 0.85rem;
                color: #495057;
                margin-bottom: 0.35rem;
                line-height: 1.35;
            }
            .org-address i {
                color: #2ecc71;
                margin-right: 2px;
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
                   class="org-filter-pill {{ empty($selectedSpecialization) ? 'org-filter-pill--active' : '' }}">
                    Все
                </a>

                @foreach($specializations as $spec)
                    @if(!empty($spec))
                        <a href="{{ route('specialists.index', [
                                'specialization' => $spec, 
                                'city_id' => $currentCityId ?? request('city_id')
                            ]) }}" 
                           class="org-filter-pill {{ $selectedSpecialization == $spec ? 'org-filter-pill--active' : '' }}">
                            {{ $spec }}
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
                            <div class="card h-100 shadow-sm hover-shadow position-relative transition">
                                
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