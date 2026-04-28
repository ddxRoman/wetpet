@extends('layouts.catalog')

@section('content')

<div class="container mt-5 mb-5">
    {{-- Хлебные крошки --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            {{-- Используем данные из объекта $animal, чтобы всё было динамично --}}
            <li class="breadcrumb-item"><a href="{{ route('animals.index') }}" class="text-decoration-none">Категории</a></li>
            <li class="breadcrumb-item">
                <a href="{{ route('animals.breeds', ['species_slug' => $animal->species_slug]) }}" class="text-decoration-none">
                    {{ $animal->species }}
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">{{ $animal->breed }}</li>
        </ol>
    </nav>

    {{-- Основная карточка породы --}}
    <div class="card border-0 shadow-sm overflow-hidden mb-5" style="border-radius: 20px;">
        <div class="row g-0">
            <div class="col-md-4 bg-light d-flex align-items-center justify-content-center border-end" style="min-height: 400px;">
                @if($animal->details && $animal->details->photo)
                    <img src="{{ asset('storage/' . $animal->details->photo) }}" class="img-fluid object-fit-cover h-100" alt="{{ $animal->breed }}">
                @else
                    <div class="text-center text-muted">
                        <i class="bi bi-camera" style="font-size: 4rem;"></i>
                        <p>Фото {{ $animal->breed }} скоро появится</p>
                    </div>
                @endif
            </div>
            <div class="col-md-8">
                <div class="card-body p-4 p-lg-5">
                    {{-- Тип из JSON --}}
                    @if(isset($animal->details->features['Тип']))
                        <div class="mb-3">
                            <span class="badge bg-primary px-3 py-2" style="border-radius: 8px; font-weight: 500; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px;">
                                {{ $animal->details->features['Тип'] }}
                            </span>
                        </div>
                    @endif
                    
                    <h1 class="fw-bold mb-4 display-5">{{ $animal->breed }}</h1>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-sm-4">
                            <div class="p-3 border rounded-4 text-center h-100 bg-white shadow-sm">
                                <small class="text-muted d-block mb-1">Вес</small>
                                <span class="fw-bold text-primary">{{ $animal->details->weight_range ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="p-3 border rounded-4 text-center h-100 bg-white shadow-sm">
                                <small class="text-muted d-block mb-1">Рост</small>
                                <span class="fw-bold text-primary">{{ $animal->details->height_range ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="p-3 border rounded-4 text-center h-100 bg-white shadow-sm">
                                <small class="text-muted d-block mb-1">Жизнь</small>
                                <span class="fw-bold text-primary">{{ $animal->details->lifespan ?? '—' }}</span>
                            </div>
                        </div>
                    </div>

                    @if($animal->details && $animal->details->short_description)
                        <div class="p-3 bg-light rounded-4 border-start border-primary border-4">
                            <p class="mb-0 fst-italic">{{ $animal->details->short_description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Описание и детали --}}
    @if($animal->details && $animal->details->full_description)
    <div class="row mb-5">
        <div class="col-lg-8">
            <h3 class="fw-bold mb-4">Особенности и характер породы</h3>
            <div class="article-text text-secondary lh-lg" style="font-size: 1.1rem;">
                {!! $animal->details->full_description !!}
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4 sticky-top" style="border-radius: 20px; top: 20px;">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Характеристики</h5>
                @if($animal->details && is_array($animal->details->features))
                    @foreach($animal->details->features as $key => $value)
                        <div class="d-flex justify-content-between py-2 border-bottom border-light">
                            <span class="text-muted small">{{ $key }}</span>
                            <span class="fw-bold small">{{ $value }}</span>
                        </div>
                    @endforeach
                @else
                    <p class="small text-muted">Сведения дополняются.</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <hr class="my-5 opacity-25">

    {{-- Секция отзывов --}}
    <div class="reviews-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Отзывы владельцев</h2>
            <button class="btn btn-dark px-4 shadow-sm" style="border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#addReviewModal">
                <i class="bi bi-pencil-square me-2"></i>Оставить отзыв
            </button>
        </div>

        @forelse($animal->reviews as $review)
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-4 border-end border-light">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 45px; height: 45px;">
                                    {{ mb_substr($review->user->name ?? 'A', 0, 1) }}
                                </div>
<div>
    <h6 class="mb-0 fw-bold">Опыт владения</h6>
    <small class="text-muted">{{ $review->user->name ?? 'Аноним' }}</small>
</div>
                            </div>
                            <div class="small text-secondary">
                                <p class="mb-1"><strong>Характер:</strong> {{ $review->temperament }}</p>
                                <p class="mb-1"><strong>Вес/Возраст:</strong> {{ $review->pet_weight }}кг / {{ $review->pet_age }}л</p>
                                <div class="mt-2 pt-2 border-top">
                                    <div class="mb-1">Интеллект: <span class="text-warning">@for($i=1;$i<=$review->intelligence;$i++)★@endfor</span></div>
                                    <div class="mb-1">Обучаемость: <span class="text-warning">@for($i=1;$i<=$review->trainability;$i++)★@endfor</span></div>
                                    <div>Дружелюбие: <span class="text-warning">@for($i=1;$i<=$review->sociability;$i++)★@endfor</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 ps-md-4">
                            <p class="text-secondary lh-base">{{ $review->comment }}</p>
                            @if($review->health_issues)
                                <div class="mt-3">
                                    <h6 class="small fw-bold text-danger mb-2"><i class="bi bi-heart-pulse-fill me-1"></i>Особенности здоровья:</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach((array)$review->health_issues as $issue)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">{{ $issue }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5 bg-light rounded-4 border border-dashed">
                <i class="bi bi-chat-dots text-muted display-4"></i>
                <p class="mt-3 text-muted">Отзывов пока нет. Расскажите о своем питомце!</p>
            </div>
        @endforelse
    </div>
</div>

{{-- Исправленное модальное окно --}}
{{-- Исправленное модальное окно --}}
<div class="modal fade" id="addReviewModal" tabindex="-1" aria-labelledby="addReviewModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="fw-bold" id="addReviewModalLabel">Поделитесь опытом о породе {{ $animal->breed }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form action="{{ route('animals.review.store', $animal->id) }}" method="POST">
                    @csrf
                    {{-- Скрытое поле для ID животного --}}
                    <input type="hidden" name="animal_id" value="{{ $animal->id }}">

                    <div class="row g-3">

                        {{-- Характер --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Общий характер</label>
                            <select name="temperament" class="form-select rounded-3">
                                @foreach($options['temperaments'] as $key => $desc)
                                    <option value="{{ $key }}">{{ $key }} ({{ $desc }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Вес и Возраст --}}
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Вес (кг)</label>
                            <input type="number" step="0.1" name="pet_weight" class="form-control rounded-3" placeholder="0.0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Возраст (лет)</label>
                            <input type="number" name="pet_age" class="form-control rounded-3" placeholder="0">
                        </div>

                        {{-- Оценки (Интеллект, Обучаемость, Дружелюбие) --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Интеллект (1-5)</label>
                            <select name="intelligence" class="form-select rounded-3">
                                @foreach($options['scales'] as $val => $label)
                                    <option value="{{ $val }}" @if($val == 5) selected @endif>{{ $val }} — {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Обучаемость (1-5)</label>
                            <select name="trainability" class="form-select rounded-3">
                                @foreach($options['scales'] as $val => $label)
                                    <option value="{{ $val }}" @if($val == 4) selected @endif>{{ $val }} — {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Дружелюбие (1-5)</label>
                            <select name="sociability" class="form-select rounded-3">
                                @foreach($options['scales'] as $val => $label)
                                    <option value="{{ $val }}" @if($val == 5) selected @endif>{{ $val }} — {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Здоровье --}}
                        <div class="col-12">
                            <label class="form-label small fw-bold text-danger">Особенности здоровья / Проблемы</label>
                            <input type="text" name="health_issues" class="form-control rounded-3" placeholder="Например: аллергия, чувствительное пищеварение (через запятую)">
                            <div class="form-text mt-1">Укажите через запятую, с какими проблемами вы столкнулись.</div>
                        </div>

                        {{-- Комментарий --}}
                        <div class="col-12">
                            <label class="form-label small fw-bold">Ваш подробный отзыв</label>
                            <textarea name="comment" class="form-control rounded-3" rows="4" placeholder="Расскажите об особенностях содержания, привычках и вашем опыте..." required></textarea>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-light px-4 me-2" data-bs-dismiss="modal" style="border-radius: 12px;">Отмена</button>
                        <button type="submit" class="btn btn-primary px-5 shadow" style="border-radius: 12px;">Опубликовать отзыв</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .breadcrumb-item a { color: #6c757d; font-size: 0.85rem; }
    .article-text { color: #4a4a4a; text-align: justify; }
    .badge { font-weight: 500; letter-spacing: 0.3px; }
    .card { transition: all 0.3s ease; }
    /* Исправление наложения фона модалки */
    .modal-backdrop { display: none !important; }
    .modal { background: rgba(0, 0, 0, 0.5); }
    .sticky-top{z-index: 10 !important;}
    .article-text img { max-width: 100%; height: auto; border-radius: 10px; }
</style>

@endsection

@section('content')
    {{-- Ваш основной контент страницы --}}
@endsection

@push('scripts')
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Article",
  "headline": "{{ $breed->name ?? 'Бельгийский гриффон' }}",
  "description": "{{ $seoMeta['description'] }}",
  "image": "{{ asset($breed->image_path ?? 'storage/default.png') }}",
  "author": {
    "@type": "Organization",
    "name": "Зверозор"
  },
  "publisher": {
    "@type": "Organization",
    "name": "Зверозор",
    "logo": {
      "@type": "ImageObject",
      "url": "{{ asset('storage/logo/logo3.png') }}"
    }
  },
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "{{ request()->url() }}"
  }
}
</script>

{{-- Рекомендую также добавить BreadcrumbList для красивых сниппетов --}}
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [{
    "@position": 1,
    "name": "Главная",
    "item": "{{ url('/') }}"
  },{
    "@position": 2,
    "name": "Собаки",
    "item": "{{ url('/animals/dog') }}"
  },{
    "@position": 3,
    "name": "{{ $breed->name ?? 'Гриффон' }}",
    "item": "{{ request()->url() }}"
  }]
}
</script>
@endpush