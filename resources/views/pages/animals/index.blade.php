@extends('layouts.catalog')
@section('content')

<div class="container mt-5 mb-5">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold">Категории животных</h2>
            <p class="text-muted">Выберите интересующий вас вид, чтобы перейти к породам</p>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        @foreach($animalTypes as $type)
            <div class="col">
                {{-- Ссылка ведет на каталог с фильтром по виду (оригинальное название из БД) --}}
                <a href="{{ route('specialists.index', ['type' => $type->species]) }}" class="text-decoration-none text-dark">
                    <div class="card h-100 border-0 shadow-sm animal-type-card">
                        <div class="card-body text-center p-4">
                            <div class="animal-icon-wrapper mb-3">
                                <img class="icon_animals" src="{{ $type->icon_url }}" alt="{{ $type->display_name }}" style="width: 50px; height: 50px; object-fit: contain;">
                            </div>
                            <h5 class="card-title fw-bold">{{ $type->display_name }}</h5>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>

<style>
    .icon_animals{
        height: 120px !important;
        width: 120px !important;
    }
    .animal-type-card {
        transition: all 0.3s ease;
        border-radius: 15px;
        background: #ffffff;
    }
    
    .animal-type-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        background: #f8f9fa;
    }

    .animal-icon-wrapper {
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f0f2f5;
        border-radius: 50%;
        width: 80px;
        margin: 0 auto;
    }
</style>

@endsection