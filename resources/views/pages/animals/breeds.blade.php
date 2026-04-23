@extends('layouts.catalog')
@section('content')

<div class="container mt-5 mb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('animals.index') }}">Категории</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $species }}</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold">Породы: {{ $species }}</h2>
            <p class="text-muted">Выберите породу</p>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
        @foreach($breeds as $breed)
            <div class="col">
                {{-- Здесь ссылка может вести уже на поиск специалистов по конкретной породе --}}
                {{-- В файле breeds.blade.php замени ссылку на эту --}}
<a href="{{ route('animals.breed.details', ['species' => $species, 'breed' => $breed->breed]) }}" class="text-decoration-none text-dark">
                    <div class="card h-100 border-0 shadow-sm breed-card">
                        <div class="card-body p-3 d-flex align-items-center">
                            <div class="breed-dot me-3"></div>
                            <span class="fw-medium">{{ $breed->breed }}</span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>

<style>
    .breed-card {
        transition: all 0.2s ease;
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.05) !important;
    }
    .breed-card:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
        color: #0d6efd !important;
    }
    .breed-dot {
        width: 8px;
        height: 8px;
        background-color: #0d6efd;
        border-radius: 50%;
    }
</style>

@endsection