@extends('layouts.catalog')
@section('content')

<div class="container mt-5 mb-5">
    {{-- Хлебные крошки для удобной навигации --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('animals.index') }}">Категории</a></li>
            <li class="breadcrumb-item"><a href="{{ route('animals.breeds', ['species' => $species]) }}">{{ $species }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $breed }}</li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 20px;">
        <div class="row g-0">
            <div class="col-md-4 bg-light d-flex align-items-center justify-content-center" style="min-height: 300px;">
                {{-- Временная иконка или плейсхолдер --}}
                <div class="text-center text-muted">
                    <i class="bi bi-image" style="font-size: 4rem;"></i>
                    <p>Место для фото породы</p>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card-body p-5">
                    <span class="badge bg-primary mb-2">{{ $species }}</span>
                    <h1 class="fw-bold mb-4">{{ $breed }}</h1>
                    
                    <div class="alert alert-info border-0" style="border-radius: 15px;">
                        <h5 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Страница в разработке</h5>
                        <p class="mb-0">Мы собираем самую полезную информацию о породе <strong>{{ $breed }}</strong>. Скоро здесь появятся описания, особенности ухода и список лучших специалистов.</p>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('specialists.index', ['breed' => $breed]) }}" class="btn btn-primary btn-lg px-4" style="border-radius: 10px;">
                            Найти специалистов
                        </a>
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-lg px-4 ms-2" style="border-radius: 10px;">
                            Назад
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection