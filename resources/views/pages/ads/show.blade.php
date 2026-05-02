@extends('layouts.app')

@section('content')
<div class="container py-5">
<nav aria-label="breadcrumb" class="mb-4 d-flex justify-content-between align-items-center">
    <ol class="breadcrumb mb-0 d-none d-md-flex"> {{-- Скрываем крошки на мобилках для акцента на кнопке --}}
        <li class="breadcrumb-item"><a href="{{ route('ads.index') }}" class="text-muted text-decoration-none">Каталог</a></li>
        <li class="breadcrumb-item active text-truncate" style="max-width: 200px;">{{ $ad->title }}</li>
    </ol>
    
    <!-- Максимально заметная кнопка -->
<!-- Максимально заметная кнопка с жесткой ссылкой на каталог -->
<a href="{{ route('ads.index') }}" 
   class="btn btn-primary btn-sm px-4 py-2 rounded-pill shadow-sm btn-back-ultra">
   <i class="bi bi-chevron-left"></i> Вернуться в каталог
</a>
</nav>

<style>
   
</style>

    <div class="row">
        {{-- Галерея до 5 фото --}}
        <div class="col-lg-7 mb-4">
            @php $photos = is_array($ad->photos) ? $ad->photos : json_decode($ad->photos, true); @endphp
            <div class=" border-0 shadow-sm overflow-hidden">
                @if($photos)
                    <img src="{{ asset('storage/' . $photos[0]) }}" id="mainImage" class="img-fluid w-100" style="height: 500px; object-fit: cover;">
                    <div class="d-flex gap-2 p-3 overflow-auto">
                        @foreach($photos as $photo)
                            <img src="{{ asset('storage/' . $photo) }}" 
                                 onclick="document.getElementById('mainImage').src = this.src" 
                                 class="img-thumbnail" style="width: 80px; height: 80px; cursor: pointer; object-fit: cover;">
                        @endforeach
                    </div>
                @else
                    <img src="{{ asset('storage/no-image.jpg') }}" class="img-fluid">
                @endif
            </div>
        </div>

        {{-- Информация --}}
        <div class="col-lg-5">
            <div class=" border-0 shadow-sm p-4">
                <h1 class="fw-bold h2 mb-3">{{ $ad->title }}</h1>
                
                <div class="d-flex align-items-center mb-4">
                    <span class="h3 fw-bold text-primary mb-0">
                        @if($ad->price_type === 'free') Бесплатно 
                        @elseif($ad->price_type === 'exchange') Обмен
                        @else {{ number_format($ad->price, 0, '.', ' ') }} ₽
                        @endif
                    </span>
                    <span class="ms-3 badge bg-light text-dark border">{{ $ad->condition === 'new' ? 'Новое' : 'Б/У' }}</span>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Город:</span>
                        <span class="fw-medium">{{ $ad->city }}</span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Животное:</span>
                        <span class="fw-medium">{{ $ad->animal->species }}</span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-muted">Адрес:</span>
                        <span class="fw-medium">{{ $ad->address ?? 'Не указан' }}</span>
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="fw-bold">Описание</h5>
                    <p class="text-secondary" style="white-space: pre-line;">{{ $ad->description }}</p>
                </div>

                <div class="p-3 bg-light rounded-3 mb-4">
                    <div class="small text-muted mb-1">Продавец: {{ $ad->user->name }}</div>
                    <div class="h5 fw-bold mb-0">📞 <a class="phone-zoom" href="tel:{{ $ad->phone }}"> {{ $ad->phone }}</a></div>
                </div>

                {{-- Управление для автора --}}
                @if(auth()->id() === $ad->user_id)
                    <div class="d-flex gap-2">
                        <a href="{{ route('ads.edit', $ad->id) }}" class="btn btn-warning flex-grow-1">Редактировать</a>
                        <form action="{{ route('ads.destroy', $ad->id) }}" method="POST" class="flex-grow-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Удалить объявление?')">Удалить</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection