@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('ads.index') }}" class="btn btn-light btn-sm rounded-circle me-3">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h2 class="fw-bold mb-0">Новое объявление</h2>
                    </div>
                    {{-- Кнопка Отмена --}}
                    <a href="{{ route('ads.index') }}" class="btn btn-outline-secondary btn-sm">Отмена</a>
                </div>
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
                <form action="{{ route('ads.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Основная инфо --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-bold small">Название объявления</label>
                            <input type="text" name="title" class="form-control" placeholder="Например: Игривый котенок в добрые руки" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Тип животного</label>
                            <select name="animal_id" class="form-select" required>
                                <option value="" selected disabled>Выберите...</option>
                                @foreach($animals as $animal)
                                    <option value="{{ $animal->id }}">{{ $animal->species }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Состояние (из таблицы: enum 'new', 'used') --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold small d-block">Состояние</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="condition" id="condNew" value="new" checked>
                            <label class="btn btn-outline-primary" for="condNew">Новое (из приюта/магазина)</label>

                            <input type="radio" class="btn-check" name="condition" id="condUsed" value="used">
                            <label class="btn btn-outline-primary" for="condUsed">Б/У (в добрые руки)</label>
                        </div>
                    </div>

                    {{-- Описание --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Описание</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Опишите характер, привычки и состояние здоровья..." required></textarea>
                    </div>

                    {{-- Фото --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Фотографии (до 5 штук)</label>
                        <input type="file" name="photos[]" class="form-control" multiple accept="image/*">
                        <div class="form-text">Первое фото будет главным.</div>
                    </div>

                    {{-- Блок локации и контактов --}}
                    <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Город</label>
                            {{-- Подставляем город из сессии, если он там есть --}}
                            <input type="text" name="city" class="form-control" value="{{ session('city_name') ?? '' }}" placeholder="Укажите город" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Точный адрес (необязательно)</label>
                            <input type="text" name="address" class="form-control" placeholder="Улица, дом">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Телефон для связи</label>
                            <input type="text" name="phone" class="form-control" placeholder="+7 (___) ___-____" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Цена</label>
                            <div class="input-group">
                                <input type="number" name="price" id="priceInput" step="1" class="form-control" value="0">
                                <span class="input-group-text">₽</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span id="priceHint" class="text-success fw-bold small">Бесплатно</span>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="is_exchange" id="exchangeCheck" value="1">
                                    <label class="form-check-label small" for="exchangeCheck">Обмен</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm fw-bold">Опубликовать объявление</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Живая логика подсказки цены
document.getElementById('priceInput').addEventListener('input', function() {
    let hint = document.getElementById('priceHint');
    if (this.value === '0' || this.value === '') {
        hint.innerText = 'Бесплатно';
        hint.className = 'text-success fw-bold small';
    } else {
        hint.innerText = 'Цена указана';
        hint.className = 'text-muted small';
    }
});
</script>
@endsection