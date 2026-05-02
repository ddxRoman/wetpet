@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="shadow-sm border-0 p-4">
        <h2 class="fw-bold mb-4">Редактировать объявление</h2>

        <form action="{{ route('ads.update', $ad->id) }}" method="POST">
            @csrf
            @method('PUT') {{-- Важно для Laravel при обновлении --}}

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Заголовок</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $ad->title) }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Тип животного</label>
                    <select name="animal_id" class="form-select" required>
                        @foreach($animals as $animal)
                            <option value="{{ $animal->id }}" {{ $ad->animal_id == $animal->id ? 'selected' : '' }}>
                                {{ $animal->species }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Описание</label>
                <textarea name="description" class="form-control" rows="5" required>{{ old('description', $ad->description) }}</textarea>
            </div>

<div class="row g-3"> {{-- g-3 уменьшает отступы между колонками --}}
    {{-- Город --}}
    <div class="col-md-4">
        <label class="form-label fw-bold small">Город</label>
        <input type="text" name="city" class="form-control form-control-sm" value="{{ old('city', $ad->city) }}" required>
    </div>

    {{-- Цена и Обмен --}}
    <div class="col-md-4">
        <label class="form-label fw-bold small">Цена</label>
        <div class="input-group input-group-sm">
            <input type="number" name="price" id="priceInput" step="1" 
                   oninput="this.value = this.value.replace(/[^0-9]/g, '')" 
                   class="form-control" value="{{ old('price', (int)$ad->price) }}">
            <span class="input-group-text">₽</span>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mt-1">
            <div id="priceHint" class="small {{ $ad->price == 0 ? 'text-success fw-bold' : 'text-muted' }}" style="font-size: 0.75rem;">
                <span id="hintText">{{ $ad->price == 0 ? 'Сейчас даром' : '0 — если даром' }}</span>
            </div>
            <div class="form-check form-switch m-0">
                <input class="form-check-input" type="checkbox" name="is_exchange" id="exchangeCheck" 
                       style="transform: scale(0.8);"
                       value="1" {{ $ad->price_type === 'exchange' ? 'checked' : '' }}>
                <label class="form-check-label small" for="exchangeCheck">Обмен</label>
            </div>
        </div>
    </div>

    {{-- Телефон --}}
    <div class="col-md-4">
        <label class="form-label fw-bold small">Телефон</label>
        <input type="text" name="phone" class="form-control form-control-sm" value="{{ old('phone', $ad->phone) }}" required>
    </div>
</div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">Сохранить изменения</button>
                <a href="{{ route('ads.show', $ad->id) }}" class="btn btn-light">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection
