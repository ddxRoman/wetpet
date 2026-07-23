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

    /* Фильтр по сфере деятельности — фирменный зелёный, не бутстраповский */
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

    /* Карточка организации: визуальная иерархия текста */
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

    /* Плашка типа деятельности — тёплый акцент в стиле сайта, не bootstrap-blue */
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

    .org-hours {
        font-size: 0.78rem;
        color: #adb5bd;
        margin-bottom: 0;
        line-height: 1.3;
    }
    .org-hours i {
        margin-right: 2px;
    }
</style>

<div class="container py-5">
    <h1 class="mb-4 text-center">Каталог зооорганизаций
        @if(!empty($selectedCity))
            <small class="text-muted d-block fs-6"> {{ $selectedCity }}</small>
        @endif
    </h1>

    @if(empty($selectedCity))
        <div class="alert alert-info text-center">
            Пожалуйста, выберите город — список организаций будет отображён только для выбранного города.
        </div>
    @else

    <div class="specialization-filter-wrapper mb-4">
        <div class="d-inline-flex gap-2 specialization-filter pb-2">
            <a href="{{ route('organizations.index', ['city_id' => $currentCityId]) }}" 
               class="org-filter-pill {{ empty($selectedTypeId) ? 'org-filter-pill--active' : '' }}">
                Все организации
            </a>
            @foreach($organizationTypes as $type)
                <a href="{{ route('organizations.index', ['type_id' => $type->id, 'city_id' => $currentCityId]) }}" 
                   class="org-filter-pill {{ $selectedTypeId == $type->id ? 'org-filter-pill--active' : '' }}">
                    {{ $type->name }}
                </a>
            @endforeach
        </div>
    </div>
    @if($organizations->isEmpty())
        <div class="alert alert-warning text-center">
            Организации в городе <strong>{{ $selectedCity }}</strong> не найдены. <br>
            <button class="btn btn-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#addOrganizationModal">
                Добавить организацию
            </button>
        </div>
    @else

    <div class="organizations-list">
        <div class="row g-4" id="organizations-container"> {{-- Добавлен ID --}}
            @include('pages.organizations._list_items', ['organizations' => $organizations])
        </div>
    </div>

    @if($organizations->hasMorePages())
        <div class="text-center mt-5 mb-5" id="load-more-container">
            <button id="load-more" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm" data-url="{{ $organizations->nextPageUrl() }}">
                Показать еще
            </button>
        </div>
    @endif

    @endif 
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const loadMoreBtn = document.getElementById('load-more');
    const container = document.getElementById('organizations-container');

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            if (!url) return;

            loadMoreBtn.disabled = true;
            loadMoreBtn.innerText = 'Загрузка...';

            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Находим новые карточки в полученном HTML
                    const newItems = doc.querySelectorAll('#organizations-container .organization-item');
                    newItems.forEach(item => container.appendChild(item));

                    // Находим новую ссылку для кнопки "Показать еще"
                    const nextBtn = doc.querySelector('#load-more');
                    if (nextBtn) {
                        loadMoreBtn.setAttribute('data-url', nextBtn.getAttribute('data-url'));
                        loadMoreBtn.disabled = false;
                        loadMoreBtn.innerText = 'Показать еще';
                    } else {
                        document.getElementById('load-more-container').remove();
                    }

                    // Инициализируем тултипы для новых элементов, если они есть
                    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                        tooltips.forEach(t => new bootstrap.Tooltip(t));
                    }
                })
                .catch(error => {
                    console.error('Error loading more organizations:', error);
                    loadMoreBtn.disabled = false;
                    loadMoreBtn.innerText = 'Ошибка. Попробовать снова';
                });
        });
    }
});
</script>
@endsection