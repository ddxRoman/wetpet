{{--
    Единый стиль для каталожных страниц (врачи, организации, специалисты):
    фильтр по специализации/сфере деятельности + карточки в списке.

    Подключается через @include('partials._catalog-card-styles') вместо
    того, чтобы копировать один и тот же <style>-блок в каждый index.blade.php.
    Палитра — фирменный синий сайта (#2563eb / #eef3ff), без стороннего
    зелёного/оранжевого, чтобы не спорить по цвету с остальным интерфейсом.
--}}
<style>
    /* Горизонтальный скролл для фильтров на мобильных устройствах */
    .specialization-filter-container,
    .specialization-filter-wrapper {
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 5px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #ccc #f1f1f1;
    }
    .specialization-filter-container::-webkit-scrollbar,
    .specialization-filter-wrapper::-webkit-scrollbar {
        height: 4px;
    }
    .specialization-filter-container::-webkit-scrollbar-thumb,
    .specialization-filter-wrapper::-webkit-scrollbar-thumb {
        background: #dee2e6;
        border-radius: 10px;
    }
    .specialization-filter {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        gap: 8px;
        padding-bottom: 12px;
        -webkit-overflow-scrolling: touch;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .specialization-filter::-webkit-scrollbar {
        display: none;
    }
    .specialization-filter .org-filter-pill {
        flex: 0 0 auto;
        white-space: nowrap;
    }

    /* Фильтр по специализации / сфере деятельности — фирменный синий сайта */
    .org-filter-pill {
        display: inline-block;
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        background-color: #eef3ff;
        border: 1px solid #d6e4ff;
        color: #2563eb;
        transition: background-color 0.2s, border-color 0.2s, color 0.2s;
    }
    .org-filter-pill:hover {
        background-color: #dbe7ff;
        border-color: #b6cdff;
        color: #1d4fd1;
    }
    .org-filter-pill--active,
    .org-filter-pill--active:hover {
        background-color: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    /* Счётчик количества записей внутри пилюли фильтра */
    .org-filter-pill__count {
        display: inline-block;
        margin-left: 0.35rem;
        padding: 0.05rem 0.45rem;
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 700;
        background-color: rgba(37, 99, 235, 0.12);
        color: inherit;
    }
    .org-filter-pill--active .org-filter-pill__count {
        background-color: rgba(255, 255, 255, 0.25);
    }

    /* Карточка в списке: визуальная иерархия текста */
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

    /* Плашка специализации / типа деятельности на карточке — тот же синий,
       но светлее и без заливки под саму кнопку-фильтр, чтобы визуально
       различались уровни (фильтр сверху / тег на карточке), оставаясь
       в одной цветовой гамме. */
    .org-type-badge {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #2563eb;
        background-color: #eef3ff;
        border: 1px solid #d6e4ff;
        padding: 0.22rem 0.6rem;
        border-radius: 20px;
        margin: 0 0.3rem 0.4rem 0;
    }

    .org-address {
        font-size: 0.85rem;
        color: #495057;
        margin-bottom: 0.35rem;
        line-height: 1.35;
    }
    .org-address i {
        color: #2563eb;
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
