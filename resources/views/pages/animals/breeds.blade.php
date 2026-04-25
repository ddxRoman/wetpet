@extends('layouts.catalog')
@section('content')

<div class="container mt-5 mb-5">
    {{-- Хлебные крошки --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('animals.index') }}" class="text-decoration-none">Категории</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $species }}</li>
        </ol>
    </nav>

    <div class="row mb-5">
        <div class="col">
            <h1 class="fw-bold display-5">{{ $species }}</h1>
            <p class="text-muted">Выберите породу из алфавитного списка</p>
        </div>
    </div>

    {{-- Группируем породы по первой букве --}}
    @php
        $groupedBreeds = $breeds->sortBy('breed')->groupBy(function($item) {
            return mb_substr($item->breed, 0, 1);
        });
    @endphp

    @foreach($groupedBreeds as $letter => $items)
        <div class="row mb-4">
            <div class="col-12">
                {{-- Заголовок буквы --}}
                <div class="d-flex align-items-center mb-3">
                    <h2 class="fw-bold text-primary mb-0 me-3" style="min-width: 40px;">{{ $letter }}</h2>
                    <div class="flex-grow-1 border-bottom opacity-25"></div>
                </div>
            </div>
            
            <div class="col-12">
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
                    @foreach($items as $breed)
                        <div class="col">
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
        </div>
    @endforeach
</div>

<style>
    .breed-card {
        transition: all 0.2s ease;
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.05) !important;
    }
    .breed-card:hover {
        background-color: #ffffff;
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important;
        border-color: #0d6efd !important;
    }
    .breed-card:hover .fw-medium {
        color: #0d6efd;
    }
    .breed-dot {
        width: 6px;
        height: 6px;
        background-color: #0d6efd;
        border-radius: 50%;
        opacity: 0.5;
    }
    .breed-card:hover .breed-dot {
        opacity: 1;
        transform: scale(1.2);
    }
</style>

@endsection