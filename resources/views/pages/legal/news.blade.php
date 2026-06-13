@extends('layouts.app')

{{-- Заголовок страницы --}}
@section('title', 'Новости и статьи — Зверозор')

@section('content')
{{-- Подключаем Vite корректно внутри секции --}}
@vite(['resources/css/main.css', 'resources/sass/app.scss', 'resources/js/app.js'])

@if(Route::is('legal/news') || Route::is('news.index'))
<div class="header_in_account">
    @include('layouts.header')
</div>
@endif
<div class="container py-4">
    <h2 class="legal_h2 mb-2">Новости</h2>
    <p class="text-muted mb-4 lead" style="font-size: 1rem;">Самые свежие материалы, полезные советы по уходу за питомцами и важные события проекта Зверозор.</p>

    @if(isset($news) && $news->count() > 0)
        <div class="row g-4">
            @foreach($news as $item)
                <div class="col-md-6 col-lg-4 d-flex">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden w-100 rounded-3 d-flex flex-column transition-hover" style="border: 1px solid #eef2f5 !important;">
                        
                        <div class="position-relative" style="height: 200px; overflow: hidden;">
                            <a href="{{ route('news.show', $item->slug) }}">
                                @if($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}" class="w-100 h-100 object-fit-cover transition-scale" alt="{{ $item->title }}">
                                @else
                                    {{-- Дефолтная заглушка Зверозора --}}
                                    <img src="{{ asset('images/default-animal.webp') }}" class="w-100 h-100 object-fit-cover opacity-75" alt="Зверозор Новости">
                                @endif
                            </a>
                            <span class="position-absolute bottom-0 start-0 bg-dark text-white px-3 py-1 m-3 rounded-pill small bg-opacity-75" style="font-size: 0.8rem;">
                                {{ $item->created_at->format('d.m.Y') }}
                            </span>
                        </div>

                        <div class="card-body p-4 d-flex flex-column flex-grow-1">
                            <h5 class="card-title fw-bold mb-2" style="font-size: 1.15rem; line-height: 1.4;">
                                <a href="{{ route('news.show', $item->slug) }}" class="text-dark text-decoration-none hover-link">
                                    {{ Str::limit($item->title, 65, '...') }}
                                </a>
                            </h5>
                            
                            <p class="text-muted card-text small flex-grow-1 mb-4" style="font-size: 0.9rem; line-height: 1.5;">
                                {{ Str::limit($item->excerpt ?? strip_tags($item->content), 110, '...') }}
                            </p>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto text-muted small" style="font-size: 0.85rem;">
                                <div>
                                    <i class="bi bi-eye me-1"></i> {{ $item->views ?? 0 }}
                                </div>
                                <a href="{{ route('news.show', $item->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size: 0.8rem;">
                                    Читать <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-5">
            {{ $news->links('pagination::bootstrap-5') }}
        </div>

    @else
        <div class="text-center py-5 my-4 bg-white rounded-3 shadow-sm border">
            <div class="mb-3 text-muted">
                <i class="bi bi-newspaper display-3"></i>
            </div>
            <h4 class="fw-bold">Раздел наполняется</h4>
            <p class="text-muted">Совсем скоро здесь появятся первые интересные статьи.</p>
            <a href="/" class="btn btn-sm btn-primary rounded-pill px-4 mt-2">На главную</a>
        </div>
    @endif
</div>@if(Route::is('legal/news') || Route::is('news.index'))
    @include('layouts.footer')
@endif


<style>
    .transition-hover {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .transition-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.08)!important;
    }
    .transition-scale {
        transition: transform 0.3s ease-in-out;
    }
    .transition-hover:hover .transition-scale {
        transform: scale(1.04);
    }
    .hover-link {
        transition: color 0.15s ease-in-out;
    }
    .hover-link:hover {
        color: #0d6efd !important;
    }
    .object-fit-cover {
        object-fit: cover;
    }
</style>
@endsection