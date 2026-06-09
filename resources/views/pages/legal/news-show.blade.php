@extends('layouts.app') {{-- Твой главный шаблон сайта --}}

@section('title', $news->title . ' — Новости Зверозор')

@section('content')
<main class="py-5 bg-light">
    <div class="container">
        
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/" class="text-decoration-none text-muted">Главная</a></li>
                <li class="breadcrumb-item"><a href="{{ route('legal/news') }}" class="text-decoration-none text-muted">Новости</a></li>
                <li class="breadcrumb-item active text-dark" aria-current="page">{{ Str::limit($news->title, 30) }}</li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-lg-8">
                <article class="card border-0 shadow-sm overflow-hidden">
                    
                    @if($news->image)
                        <img src="{{ asset('storage/' . $news->image) }}" class="img-fluid w-100" alt="{{ $news->title }}" style="max-height: 400px; object-fit: cover;">
                    @endif

                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center text-muted small mb-3">
                            <span class="me-3">
                                <i class="bi bi-calendar3 me-1"></i> 
                                {{ $news->created_at ? $news->created_at->format('d.m.Y') : 'Недавно' }}
                            </span>
                            @if(isset($news->views))
                                <span>
                                    <i class="bi bi-eye me-1"></i> {{ $news->views }} просмотров
                                </span>
                            @endif
                        </div>

                        <h1 class="h2 fw-bold text-dark mb-4">{{ $news->title }}</h1>

                        @if($news->excerpt)
                            <p class="lead text-secondary fw-normal mb-4 border-start border-4 border-primary ps-3">
                                {{ $news->excerpt }}
                            </p>
                        @endif

                        <div class="news-content text-secondary lh-lg fs-5">
                            {!! nl2br(e($news->content)) !!} 
                            {{-- Если в базе хранится готовый HTML, используй: {!! $news->content !!} --}}
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('legal/news') }}" class="btn btn-outline-primary px-4 rounded-pill">
                                <i class="bi bi-arrow-left me-2"></i> Назад к новостям
                            </a>
                            
                            <div class="share-buttons">
                                <span class="small text-muted me-2 d-none d-sm-inline">Поделиться:</span>
                                <a href="https://t.me/share/url?url={{ request()->url() }}&text={{ urlencode($news->title) }}" target="_blank" class="btn btn-sm btn-light text-primary rounded-circle" title="Telegram">
                                    <i class="bi bi-telegram"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </article>
            </div>

            <div class="col-lg-4">
                <aside class="sticky-top" style="top: 2rem; z-index: 10;">
                    <div class="card border-0 shadow-sm p-4 mb-4">
                        <h3 class="h5 fw-bold text-dark mb-3">Читайте также</h3>
                        <div class="list-group list-group-flush">
                            
                            {{-- Переменную $recentNews нужно будет передать из метода show в NewsController --}}
                            @isset($recentNews)
                                @forelse($recentNews as $recent)
                                    <a href="{{ route('news.show', $recent->slug) }}" class="list-group-item list-group-item-action border-0 px-0 py-3 d-flex align-items-start text-decoration-none">
                                        <div class="flex-grow-1">
                                            <h4 class="h6 fw-semibold text-dark mb-1 text-truncate-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                {{ $recent->title }}
                                            </h4>
                                            <span class="small text-muted">
                                                {{ $recent->created_at ? $recent->created_at->format('d.m.Y') : '' }}
                                            </span>
                                        </div>
                                    </a>
                                @empty
                                    <p class="text-muted small my-2">Других новостей пока нет.</p>
                                @endforelse
                            @else
                                <p class="text-muted small my-2">Похожие публикации обновляются.</p>
                            @endisset
                        </div>
                    </div>
                    <div class="card border-0 bg-primary text-white p-4 shadow-sm text-center">
                        <h4 class="fw-bold mb-2">Зверозор</h4>
                        <p class="small mb-3">Сервис поиска ветеринарных услуг, анкет врачей и подбора правильного ухода.</p>
                        <a href="/" class="btn btn-light text-primary btn-sm rounded-pill fw-semibold px-4">На главную</a>
                    </div>
                </aside>
            </div>
        </div>

    </div>
</main>
@endsection