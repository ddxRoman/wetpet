@extends('layouts.catalog')

@section('content')

@php
    $avatar = $profileUser->avatar
        ? asset('storage/' . $profileUser->avatar)
        : asset('storage/avatars/default/default_avatar.webp');

    $typeLabels = [
        'Clinic'       => 'Клиника',
        'Doctor'       => 'Врач',
        'Specialist'   => 'Специалист',
        'Organization' => 'Организация',
    ];

    $genderLabels = [
        'male'   => 'Самец',
        'female' => 'Самка',
    ];
@endphp

<div class="container mt-5 mb-5" style="max-width: 900px;">

    {{-- ===================== Шапка профиля ===================== --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
        <div class="card-body p-4 d-flex flex-column flex-sm-row align-items-center gap-4" data-zoom-scope>
            <a href="{{ $avatar }}" class="js-zoom flex-shrink-0" data-title="{{ $profileUser->name }}" title="Увеличить фото">
                <img src="{{ $avatar }}"
                     alt="{{ $profileUser->name }}"
                     width="120" height="120"
                     class="rounded-circle border"
                     style="object-fit: cover; cursor: zoom-in; border-color: #1ccfc9 !important; border-width: 3px !important;">
            </a>

            <div class="text-center text-sm-start" data-zoom-info>
                <h1 class="h3 fw-bold mb-2">{{ $profileUser->name }}</h1>

                @if($registeredFor)
                    <div class="text-muted">
                        <i class="bi bi-calendar-check me-1" style="color: #1ccfc9;"></i>
                        На сайте: <strong>{{ $registeredFor }}</strong>
                    </div>
                @endif

                <div class="text-muted small mt-2">
                    Питомцев: {{ $pets->count() }} &nbsp;·&nbsp; Отзывов: {{ $reviews->total() }}
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== Питомцы ===================== --}}
    <h2 class="h4 fw-bold mb-3">Питомцы</h2>

    @if($pets->isEmpty())
        <p class="text-muted mb-5">Пользователь пока не добавил питомцев.</p>
    @else
        <div class="row g-3 mb-5">
            @foreach($pets as $pet)
                @php
                    // Фото питомца, либо дефолт по виду (кошка/собака/другое) —
                    // см. App\Models\Pet::getPhotoUrlAttribute()
                    $petPhoto = $pet->photo_url;
                @endphp
                <div class="col-6 col-md-4">
                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;" data-zoom-scope>
                        <a href="{{ $petPhoto }}" class="js-zoom d-block" data-title="{{ $pet->name }}" title="Увеличить фото">
                            <img src="{{ $petPhoto }}"
                                 alt="{{ $pet->name }}"
                                 class="card-img-top{{ $pet->death_date ? ' pet-deceased' : '' }}"
                                 style="height: 180px; object-fit: cover; cursor: zoom-in;">
                        </a>
                        <div class="card-body" data-zoom-info>
                            <div class="fw-semibold">{{ $pet->name }}</div>

                            @if($pet->animal)
                                <div class="small text-muted">
                                    {{ $pet->animal->species }}@if($pet->animal->breed) ({{ $pet->animal->breed }})@endif
                                </div>
                            @endif

                            @if(isset($genderLabels[$pet->gender]))
                                <div class="small text-muted">{{ $genderLabels[$pet->gender] }}</div>
                            @endif

                            @if($pet->death_date)
                                <div class="small text-muted">Дата смерти: {{ $pet->death_date->format('d.m.Y') }}</div>
                                @if($pet->age_label)
                                    <div class="small text-muted">Возраст на момент смерти: {{ $pet->age_label }}</div>
                                @endif
                            @elseif($pet->age_label)
                                <div class="small text-muted">Возраст: {{ $pet->age_label }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ===================== Отзывы ===================== --}}
    <h2 class="h4 fw-bold mb-3">Отзывы</h2>

    @if($reviews->isEmpty())
        <p class="text-muted">Пользователь пока не оставил отзывов.</p>
    @else
        @foreach($reviews as $review)
            @php
                $target = $review->reviewable;
                $type   = class_basename($review->reviewable_type);

                // Если у объекта пустой city (или slug), маршрут собрать нельзя —
                // тогда выводим название без ссылки, а не роняем всю страницу.
                try {
                    $targetUrl = match ($type) {
                        'Doctor'       => route('doctors.show', $target->slug),
                        'Specialist'   => route('specialists.show', $target->slug),
                        'Clinic'       => route('clinics.show', ['city' => $target->city_slug, 'clinic' => $target->slug]),
                        'Organization' => route('organizations.show', ['city' => $target->city_slug, 'slug' => $target->slug]),
                        default        => null,
                    };
                } catch (\Illuminate\Routing\Exceptions\UrlGenerationException $e) {
                    $targetUrl = null;
                }
            @endphp

            <div class="card border-0 shadow-sm mb-3" style="border-radius: 16px;">
                <div class="card-body p-4">

                    {{-- Кому отзыв --}}
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2 w-100">
                        {{-- Блок названия занимает всё свободное место, дата справа не сжимает его --}}
                        <div style="flex: 1 1 0; min-width: 0;">
                            <span class="badge bg-light text-dark border me-1">{{ $typeLabels[$type] ?? $type }}</span>
                            @if($targetUrl)
                                <a href="{{ $targetUrl }}" class="fw-semibold text-decoration-none text-primary">{{ $target->name }}</a>
                            @else
                                <span class="fw-semibold">{{ $target->name }}</span>
                            @endif

                            @if($review->target_address)
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-geo-alt-fill me-1" style="color: #1ccfc9;"></i>{{ $review->target_address }}
                                </div>
                            @endif
                        </div>
                        <div class="small text-muted flex-shrink-0 text-nowrap">{{ optional($review->review_date)->format('d.m.Y') }}</div>
                    </div>

                    {{-- Оценка --}}
                    @if($review->rating)
                        <div class="mb-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <img src="{{ asset('storage/icon/button/' . ($i <= $review->rating ? 'award-stars_active.svg' : 'award-stars_disable.svg')) }}"
                                     width="20" alt="звезда">
                            @endfor
                        </div>
                    @endif

                    @if($review->liked)
                        <div style="white-space: pre-line;"><strong class="text-success">Понравилось:</strong> {{ $review->liked }}</div>
                    @endif
                    @if($review->disliked)
                        <div style="white-space: pre-line;"><strong class="text-danger">Не понравилось:</strong> {{ $review->disliked }}</div>
                    @endif
                    @if($review->content)
                        <p class="mt-2 mb-0" style="white-space: pre-line;">{{ $review->content }}</p>
                    @endif

                    @if($review->pet)
                        <div class="small text-muted mt-2" data-zoom-scope>
                            <em>Питомец:</em>
                            <a href="{{ $review->pet->photo_url }}" class="js-zoom text-reset text-decoration-none" style="color:inherit; text-decoration:none;" data-title="{{ $review->pet->name }}" title="Посмотреть фото питомца">{{ $review->pet->name }}@if($review->pet->animal)
                                ({{ $review->pet->animal->species }} — {{ $review->pet->animal->breed }})
                            @endif</a>

                            {{-- Скрытая подпись для модалки — тот же вид, что в карточке питомца выше --}}
                            <div class="d-none" data-zoom-info>
                                <div class="fw-semibold">{{ $review->pet->name }}</div>
                                @if($review->pet->animal)
                                    <div class="small text-muted">{{ $review->pet->animal->species }}@if($review->pet->animal->breed) ({{ $review->pet->animal->breed }})@endif</div>
                                @endif
                                @if(isset($genderLabels[$review->pet->gender]))
                                    <div class="small text-muted">{{ $genderLabels[$review->pet->gender] }}</div>
                                @endif
                                @if($review->pet->death_date)
                                    <div class="small text-muted">Дата смерти: {{ $review->pet->death_date->format('d.m.Y') }}</div>
                                    @if($review->pet->age_label)
                                        <div class="small text-muted">Возраст на момент смерти: {{ $review->pet->age_label }}</div>
                                    @endif
                                @elseif($review->pet->age_label)
                                    <div class="small text-muted">Возраст: {{ $review->pet->age_label }}</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Фото отзыва --}}
                    @if($review->photos && $review->photos->count())
                        <div class="mt-3 d-flex flex-wrap gap-2">
                            @foreach($review->photos as $photo)
                                <a href="{{ asset('storage/' . $photo->photo_path) }}" target="_blank" rel="noopener">
                                    <img src="{{ asset('storage/' . $photo->photo_path) }}"
                                         class="rounded border"
                                         style="width: 100px; height: 100px; object-fit: cover;"
                                         alt="Фото отзыва">
                                </a>
                            @endforeach
                        </div>
                    @endif

                </div>
            </div>
        @endforeach

        <div class="mt-4 d-flex justify-content-center">
            {{ $reviews->links() }}
        </div>
    @endif

</div>

{{-- Модалка просмотра фото (аватар, питомцы, отзывы) подключена глобально
     в layouts/footer.blade.php — partials/image-zoom-modal.blade.php --}}

@endsection