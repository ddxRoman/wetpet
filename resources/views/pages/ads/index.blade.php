@extends('layouts.app')
@include('layouts.header')
@section('content')
<div class="container py-4">
    <div class="row">
        {{-- Сетка фильтров --}}
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Фильтры</h5>
                    <form action="{{ route('ads.index') }}" method="GET">
                        {{-- Поиск --}}
                        <div class="mb-3">
                            <label class="small fw-bold">Поиск</label>
                            <input type="text" name="search" class="form-control" placeholder="Название..." value="{{ request('search') }}">
                        </div>

                        {{-- Тип животного --}}
                        <div class="mb-3">
                            <label class="small fw-bold">Тип животного</label>
                            <select name="animal_id" class="form-select">
                                <option value="">Все животные</option>
                                @foreach($animals as $animal)
                                    <option value="{{ $animal->id }}" {{ request('animal_id') == $animal->id ? 'selected' : '' }}>
                                        {{ $animal->species }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Город --}}
                        <div class="mb-3">
                            <label class="small fw-bold">Город</label>
                            <input type="text" name="city" class="form-control" placeholder="Напр: Краснодар" value="{{ request('city') }}">
                        </div>

<div class="mb-3">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="is_free" id="isFree" value="1" {{ request('is_free') ? 'checked' : '' }}>
        <label class="form-check-label small fw-bold" for="isFree">
            Бесплатно
        </label>
    </div>
</div>

<button type="submit" class="btn btn-primary w-100">Применить</button>  <a href="{{ route('ads.index') }}" class="btn btn-link w-100 btn-sm text-decoration-none mt-2">Сбросить</a>
                    </form>
                </div>
            </div>
        </div>

        {{-- Список объявлений --}}
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Объявления</h2>
                <a href="{{ route('ads.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Подать объявление
                </a>
            </div>

            <div class="row row-cols-1 row-cols-md-3 g-4">
                @forelse($ads as $ad)
<div class="col">
    <div class="card h-100 border-0 shadow-sm ad-card overflow-hidden">
        {{-- Обертка для фото --}}
        <div class="ad-card-img-wrapper">
            <img src="{{ !empty($ad->photos) ? asset('storage/' . $ad->photos[0]) : asset('storage/no-image.jpg') }}" 
                 class="ad-card-img" 
                 alt="{{ $ad->title }}">
        </div>
        
        <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge bg-light text-dark border small">{{ $ad->condition === 'new' ? 'Новое' : 'Б/У' }}</span>
                <span class="text-primary fw-bold">
                    @if($ad->price_type === 'free') Бесплатно 
                    @elseif($ad->price_type === 'exchange') Обмен
                    @else {{ number_format($ad->price, 0, '.', ' ') }} ₽
                    @endif
                </span>
            </div>

            <h5 class="card-title fw-bold ad-card-title mb-2">{{ $ad->title }}</h5>
            
            <div class="card-text text-muted small mb-3">
                <div class="text-truncate">📍 {{ $ad->city }}</div>
                <div class="text-truncate">🐾 {{ $ad->animal?->species ?? 'Не указано' }}</div>
            </div>

            <div class="mt-auto"> {{-- Прижимает кнопку к низу, если контента мало --}}
                <a href="{{ route('ads.show', $ad->id) }}" class="btn btn-outline-primary btn-sm w-100">Подробнее</a>
            </div>
        </div>
    </div>
</div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">Объявлений пока нет. Будьте первым!</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $ads->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

<style>
    /* Фиксированная высота обертки изображения */
    .ad-card-img-wrapper {
        height: 200px; /* Оптимально для сетки из 3 колонок */
        overflow: hidden;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Масштабирование картинки без потери пропорций */
    .ad-card-img {
        width: 100%;
        height: 100%;
        object-fit: cover; /* Заполняет область, обрезая лишнее */
        transition: transform 0.3s ease;
    }

    /* Эффект при наведении */
    .ad-card:hover .ad-card-img {
        transform: scale(1.05);
    }

    /* Чтобы заголовки тоже не разваливали сетку */
    .ad-card-title {
        height: 2.5rem; /* Высота для двух строк */
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.2;
    }
</style>