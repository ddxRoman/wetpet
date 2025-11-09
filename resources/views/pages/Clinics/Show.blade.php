@php
use App\Models\Pet;
@endphp
@extends('layouts.app')
@section('content')
<body>
<div class="d-flex flex-column min-vh-100 bg-white">
    @include('layouts.header')
    <main class="flex-grow-1 container py-5">
        <main class="flex-grow-1 container py-5">
            {{-- 🔙 Кнопка "В каталог" --}}
            <div class="mb-3">
                <a href="{{ route('clinics.index') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 shadow-sm back-to-catalog">
                    <img src="{{ asset('storage/icon/button/arrow-back.svg') }}" width="22" alt="paw">
                    В каталог
                </a>
            </div>
            <div class="row">
                <div class="col-lg-12 col-12">
                    {{-- Логотип и название --}}
                    @php
                    $logo = !empty($clinic->logo)
                    ? asset('storage/' . $clinic->logo)
                    : asset('storage/clinics/logo/default.webp');
                    @endphp
                    <div class="d-flex align-items-center mb-4 flex-wrap">
                        <img src="{{ $logo }}" alt="{{ $clinic->name }}" class="logo_clinic_card me-3 mb-3 mb-md-0">
                        <h1 class="text-2xl fw-bold m-0">{{ $clinic->name }}</h1>
                        @php
                        use App\Models\Review;
                        // Получаем все отзывы по клинике
                        $reviews = Review::where('reviewable_id', $clinic->id)
                        ->where('reviewable_type', \App\Models\Clinic::class)
                        ->get();
                        $reviewCount = $reviews->count();
                        $averageRating = $reviewCount > 0 ? round($reviews->avg('rating'), 1) : null;
                        @endphp
                        @if($reviewCount > 0)
                        <div class="d-flex align-items-center mt-2 rating-summary">
                            {{-- Звёзды --}}
                            <div class="d-flex align-items-center me-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    <img src="{{ asset('storage/icon/button/' . ($i <= $averageRating ? 'award-stars_active.svg' : 'award-stars_disable.svg')) }}"
                                    width="22" alt="звезда">
                                    @endfor
                            </div>
                            {{-- Средний рейтинг и количество отзывов --}}
                            <span class="fw-semibold text-dark me-1">{{ $averageRating }}</span>
                            <span class="text-muted small">({{ $reviewCount }} отзыв{{ $reviewCount % 10 == 1 && $reviewCount % 100 != 11 ? '' : 'ов' }})</span>
                        </div>
                        @else
                        <div class="text-muted small mt-2">Отзывов пока нет</div>
                        @endif
                    </div>


{{-- Вкладки --}}
<ul class="nav nav-tabs mb-4" id="clinicTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts" role="tab">Контакты</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" role="tab">Услуги</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" role="tab">Отзывы</a>
    </li>
<!-- <li class="nav-item" role="presentation">
    <a class="nav-link" id="awards-tab" data-bs-toggle="tab" data-bs-target="#awards" role="tab">Награды</a>
</li>

    <li class="nav-item" role="presentation">
        <a class="nav-link" id="photos-tab" data-bs-toggle="tab" data-bs-target="#photos" role="tab">Фото</a>
    </li> -->
</ul>



                    <div class="tab-content" id="clinicTabsContent">
                        {{-- Контакты --}}
                        <div class="tab-pane fade show active" id="contacts" role="tabpanel">
                            <div class="row">
                                {{-- Левая часть: контакты --}}
                                <div class="col-md-7">
                                    <div class="text-secondary mb-4">
                                        <div>📍 {{ $clinic->country }}, {{ $clinic->region }}, {{ $clinic->city }}, {{ $clinic->street }} {{ $clinic->house }}</div>
                                        <div>🕒 {{ $clinic->workdays }} — {{ $clinic->schedule }}</div>
                                        {{-- Телефоны как ссылки --}}
                                        @if($clinic->phone1)
                                        <div>
                                            📞 <a href="tel:{{ preg_replace('/\D/', '', $clinic->phone1) }}">{{ $clinic->phone1 }}<img width="24px" src="{{ asset('storage/icon/contacts/phone.svg') }}" alt="Рейтинг"> </a>
                                            @if($clinic->phone2)
                                            , <a href="tel:{{ preg_replace('/\D/', '', $clinic->phone2) }}">{{ $clinic->phone2 }}<img width="24px" src="{{ asset('storage/icon/contacts/phone.svg') }}" alt="Рейтинг"> </a>
                                            @endif
                                        </div>
                                        @endif
                                        <div>✉️ {{ $clinic->email }}</div>
                                        @if($clinic->telegram)
                                        <div>💬 Telegram: <a href="https://t.me/{{ $clinic->telegram }}" target="_blank">https://t.me/{{ $clinic->telegram }}<img width="24px" src="{{ asset('storage/icon/contacts/telegram.svg') }}" alt="Рейтинг"></a></div>
                                        @endif
                                        @if($clinic->whatsapp)
                                        <div>💬 WhatsApp: <a href="https://wa.me/{{ $clinic->whatsapp }}" target="_blank">{{ $clinic->whatsapp }}<img width="24px" src="{{ asset('storage/icon/contacts/whatsapp.svg') }}" alt="Рейтинг"></a></div>
                                        @endif
                                        @if($clinic->website)
                                        <div>💬 <a href="{{ $clinic->website }}">Перейти на сайт</a></div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Карта / Доп. информация --}}
                                <div class="card shadow-sm border-0 p-3" style="max-width: 450px;">
                                    <h5 class="fw-semibold mb-2">Карта / Доп. информация</h5>

                                    {{-- Встраиваемая Яндекс.Карта с геометкой --}}
                                    <div class="bg-light rounded overflow-hidden mb-3" style="height: 300px; width: 100%;">
                                        <iframe
                                            src="https://yandex.ru/map-widget/v1/?text={{ urlencode($clinic->country . ', ' . $clinic->region . ', ' . $clinic->city . ', ' . $clinic->street . ' ' . $clinic->house) }}&z=16&l=map"
                                            width="100%"
                                            height="100%"
                                            frameborder="0"
                                            allowfullscreen
                                            loading="lazy"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Услуги --}}
                        <div class="tab-pane fade" id="services" role="tabpanel">
                            @php
                            // Все услуги, связанные с клиникой
                            $services = $clinic->services ?? collect();

                            // Сортировка по специализации и названию
                            $services = $services->sortBy([
                            fn($a, $b) => strcasecmp($a->specialization ?? '', $b->specialization ?? ''),
                            fn($a, $b) => strcasecmp($a->name ?? '', $b->name ?? ''),
                            ]);

                            // Загружаем цены
                            $prices = \App\Models\Price::where('clinic_id', $clinic->id)->get()->keyBy('service_id');

                            // Группировка по специализациям
                            $grouped = $services->groupBy(fn($s) => $s->specialization ?? 'Без специализации');

                            // Алфавит (только используемые буквы)
                            $letters = collect($grouped->keys())
                            ->map(fn($key) => mb_strtoupper(mb_substr($key, 0, 1)))
                            ->unique()
                            ->sort()
                            ->values();
                            @endphp

                            @if($grouped->isNotEmpty())

                            {{-- 🐾 Алфавитный навигатор --}}
                            <div class="mb-4 d-flex flex-wrap gap-2 justify-content-start">
                                @foreach($letters as $letter)
                                <a href="#letter-{{ $letter }}" class="paw-link text-decoration-none" title="Перейти к '{{ $letter }}'">
                                    <div class="paw-circle">
                                        <img src="{{ asset('storage/icon/alphabet/letter_icon.png') }}" class="paw-icon" alt="paw">
                                        <span class="paw-letter">{{ $letter }}</span>
                                    </div>
                                </a>
                                @endforeach
                            </div>

                            {{-- Список специализаций --}}
                            @foreach($grouped as $specialization => $group)
                            @php
                            $anchor = mb_strtoupper(mb_substr($specialization, 0, 1));
                            @endphp
                            <div id="letter-{{ $anchor }}" class="mb-5 specialization-block">
                                <h5 class="fw-semibold specialization_block text-primary border-bottom pb-2 mb-3 specialization-header">
                                    {{ $specialization }}
                                </h5>

                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60%">Название услуги</th>
                                            <th style="width: 40%">Стоимость</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($group as $service)
                                        @php
                                        $price = $prices->get($service->id);
                                        @endphp
                                        <tr>
                                            <td>{{ $service->name }}</td>
                                            <td>
                                                @if($price && $price->price !== null)
                                                {{ number_format($price->price, 0, ',', ' ') }} {{ $price->currency }}
                                                @else
                                                —
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endforeach

                            @else
                            <p class="text-muted">Информация об услугах отсутствует.</p>
                            @endif
                        </div>


{{-- Награды --}}
<div class="tab-pane fade" id="awards" role="tabpanel">
    <div class="row g-4">
        @forelse($clinic->awards ?? [] as $index => $award)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card h-100 shadow-sm border-0">
                    <a href="#" 
                       class="award-thumb" 
                       data-bs-toggle="modal" 
                       data-bs-target="#awardModal"
                       data-index="{{ $index }}">
                        <img src="{{ asset('storage/' . $award->image) }}" 
                             class="card-img-top rounded" 
                             alt="{{ $award->title }}">
                    </a>
                    <div class="card-body">
                        <h6 class="card-title text-center fw-bold">{{ $award->title }}</h6>
                        <p class="text-muted small text-center mb-0">
                            {{ Str::limit($award->description, 60, '...') }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center text-muted">Награды пока не добавлены.</p>
        @endforelse
    </div>

    {{-- Модальное окно со слайдером --}}
    @if(($clinic->awards ?? [])->count())
    <div class="modal fade" id="awardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-0">
                    <div id="awardCarousel" class="carousel slide" data-bs-ride="false">
                        <div class="carousel-inner">
                            @foreach($clinic->awards as $index => $award)
                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                    <div class="text-center p-3">
                                        <img src="{{ asset('storage/' . $award->image) }}" 
                                             class="img-fluid rounded mb-3 award-img"
                                             alt="{{ $award->title }}"
                                             style="max-height: 70vh; object-fit: contain;">
                                        <h5 class="fw-bold">{{ $award->title }}</h5>
                                        <p class="text-muted mb-0">{{ $award->description }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Стрелки навигации --}}
                        <button class="carousel-control-prev" type="button" data-bs-target="#awardCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#awardCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

                        {{-- Отзывы --}}
                        <div class="tab-pane fade" id="reviews" role="tabpanel">
                            @php


                            $reviews = Review::where('reviewable_id', $clinic->id)
                            ->where('reviewable_type', \App\Models\Clinic::class)
                            ->with(['user', 'photos'])
                            ->latest('review_date')
                            ->get();

// Получаем питомцев с данными из таблицы animals
$pets = Pet::where('user_id', auth()->id())
    ->with('animal') // подгружаем связь
    ->get();
@endphp


{{-- 📝 Кнопка открытия / закрытия формы --}}
@auth
<div class="text-end mb-3">
    <button id="toggleReviewButton"
            class="btn btn-outline-primary"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#addReviewForm"
            aria-expanded="false"
            aria-controls="addReviewForm">
        ✍️ Оставить отзыв
    </button>
</div>

{{-- 🔽 Скрытая форма --}}
<div class="collapse" id="addReviewForm">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="fw-semibold mb-3">Оставить отзыв</h5>

            <form id="reviewForm"
                  method="POST"
                  action="{{ route('reviews.store') }}"
                  enctype="multipart/form-data">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger small py-2">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <input type="hidden" name="reviewable_id" value="{{ $clinic->id }}">
                <input type="hidden" name="reviewable_type" value="{{ \App\Models\Clinic::class }}">

{{-- ⭐ Оценка --}}
<div class="mb-3">
    <label class="form-label fw-semibold">Оценка:</label>
    <div id="addRatingStars" class="d-flex gap-1">
        @for($i = 1; $i <= 5; $i++)
            <img src="{{ asset('storage/icon/button/award-stars_disable.svg') }}"
                 data-value="{{ $i }}"
                 class="rating-star"
                 width="28"
                 alt="звезда">
        @endfor
    </div>
    <input type="hidden" name="rating" id="addRatingValue" value="0">
    @error('rating')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
    <div id="addRatingError" class="text-danger small mt-1 d-none">
        Выберите оценку от 1 до 5 звёзд.
    </div>
</div>


                {{-- 💚 Понравилось --}}
                <div class="mb-3">
                    <label class="form-label">Понравилось:</label>
                    <input type="text" name="liked" class="form-control" placeholder="Что вам понравилось">
                </div>

                {{-- 💔 Не понравилось --}}
                <div class="mb-3">
                    <label class="form-label">Не понравилось:</label>
                    <input type="text" name="disliked" class="form-control" placeholder="Что можно улучшить">
                </div>

                {{-- 💬 Текст отзыва --}}
                <div class="mb-3">
                    <label class="form-label">Ваш отзыв:</label>
                    <textarea name="content" id="reviewText" class="form-control small-textarea"
                              placeholder="Напишите свой отзыв..." rows="2"></textarea>
                </div>
{{-- 🐾 Питомец --}}
<div class="mb-3">
    <label class="form-label">Ваш питомец:</label>
    <select name="pet_id" class="form-select">
        @forelse($pets as $pet)
            <option value="{{ $pet->id }}">
                {{ $pet->name }}
                @if($pet->animal)
                    — {{ $pet->animal->breed }}
                @endif
            </option>
        @empty
            <option disabled>Добавьте питомца в профиле</option>
        @endforelse
    </select>
</div>


                {{-- 📎 Загрузка чека --}}
                <div class="mb-3">
                    <label class="form-label">Чек (для подтверждения отзыва):</label>
                    <input type="file" name="receipt" accept="image/*,application/pdf" class="form-control">
                </div>

                {{-- 🖼 Фото --}}
                <div class="mb-3">
                    <label class="form-label">Фотографии:</label>
                    <input type="file" name="photos[]" multiple accept="image/*" class="form-control">
                </div>

                {{-- 🚀 Кнопка --}}
                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-4">Отправить отзыв</button>
                </div>
            </form>
        </div>
    </div>
</div>
@else
<p class="text-muted mb-4">
    Чтобы оставить отзыв, <a href="{{ route('login') }}">войдите в аккаунт</a>.
</p>
@endauth


                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="1" id="verifiedOnly">
                                <label class="form-check-label" for="verifiedOnly" title="Будут показываться только те отзывы на которых был прикреплен чек подтверждающий визит в клинику">
                                    Показывать только  "Реальных клиентов"
                                </label>
                            </div>


                            {{-- 🔽 Сортировка отзывов --}}
<div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
    <label for="sortReviews" class="form-label mb-0 me-2 fw-semibold">Сортировать по:</label>
    <select id="sortReviews" class="form-select w-auto">
        <option value="date_desc" selected>Дате (новые сверху)</option>
        <option value="date_asc">Дате (старые сверху)</option>
        <option value="rating_desc">Оценке (от высокой к низкой)</option>
        <option value="rating_asc">Оценке (от низкой к высокой)</option>
    </select>
</div>

                            
                            {{-- 🔽 Список отзывов --}}

                            <div id="reviewList" class="list-group">
                                @foreach($reviews as $review)
                                <div class="list-group-item mb-3 border rounded shadow-sm p-4 review-card"
                                    data-date="{{ $review->review_date->timestamp }}"
                                    data-rating="{{ $review->rating }}"
                                    data-verified="{{ $review->receipt_verified }}">

                                    @if($review->receipt_verified == "verified")
                                    <div class="verified-badge position-absolute top-0 end-0 bg-success text-white small px-2 py-1 rounded-start" title="Этот пользователь подтвердил свой визит в клинику, чеком, электронной квитанцией или заключаем из больницы">
                                        ✅ Реальный клиент
                                    </div>
                                    @endif


                                    {{-- Пользователь --}}
                                    <div class="d-flex align-items-center mb-3">
                                        @php
                                        $avatar = $review->user->avatar
                                        ? asset('storage/'.$review->user->avatar)
                                        : asset('storage/avatars/default/default_avatar.webp');
                                        @endphp
                                        <img src="{{ $avatar }}" width="56" height="56" class="rounded-circle me-3 border" alt="{{ $review->user->name }}">
                                        <div>
                                            <a href="{{ route('user.profile', $review->user->id) }}" class="fw-semibold text-decoration-none text-primary">
                                                {{ $review->user->name }}
                                            </a>
                                            <div class="small text-muted">{{ $review->review_date->format('d.m.Y') }}</div>
                                            @if(Auth::id() === $review->user_id)

                                            {{-- Отметка "Реальный клиент" --}}
                                            @if($review->receipt_verified == 1)
                                            <span class="verifed_client">

                                                ✅ Реальный клиент
                                            </span>
                                            @endif
                                            <div class="mt-1">
                                                <!-- <button class="btn btn-sm btn-outline-primary edit-review"
                                                    data-id="{{ $review->id }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editReviewModal">
                                                    ✏️ Редактировать
                                                </button> -->

                                                <form action="{{ route('reviews.destroy', $review->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Удалить отзыв?')">Удалить</button>
                                                </form>
                                            </div>
                                            @endif

                                        </div>
                                    </div>

                                    {{-- ⭐ Оценка --}}
                                    <div class="mb-3">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <img src="{{ asset('storage/icon/button/' . ($i <= $review->rating ? 'award-stars_active.svg' : 'award-stars_disable.svg')) }}"
                                            width="20" alt="звезда">
                                            @endfor
                                    </div>

                                    @if($review->liked)
                                    <div data-field="liked"><strong class="text-success">Понравилось:</strong> {{ $review->liked }}</div>
                                    @endif
                                    @if($review->disliked)
                                    <div data-field="disliked"><strong class="text-danger">Не понравилось:</strong> {{ $review->disliked }}</div>
                                    @endif

                                    @if($review->content)
                                    <p class="mt-2">{{ $review->content }}</p>
                                    @endif

                                    @php
$reviews = Review::where('reviewable_id', $clinic->id)
    ->where('reviewable_type', \App\Models\Clinic::class)
    ->with(['user', 'photos', 'pet.animal']) // добавили pet и animal
    ->latest('review_date')
    ->get();
@endphp


@if($review->pet)
    <div class="small text-muted mt-2">
        <em>Питомец:</em>
        {{ $review->pet->name }}
        @if($review->pet->animal)
            ({{ $review->pet->animal->species }} — {{ $review->pet->animal->breed }})
        @endif
    </div>
@endif


                                    {{-- Фото отзыва --}}
@if($review->photos && $review->photos->count())
    <div class="mt-3 d-flex flex-wrap gap-2 review-photos" data-review-id="{{ $review->id }}">
        @foreach($review->photos as $photo)
            <img src="{{ asset('storage/' . $photo->photo_path) }}"
                 class="rounded border review-photo"
                 data-review-id="{{ $review->id }}"
                 data-index="{{ $loop->index }}"
                 style="width: 100px; height: 100px; object-fit: cover; cursor: zoom-in;"
                 alt="Фото отзыва">
        @endforeach
    </div>
@endif

                                </div>
                                @endforeach
                            </div>
                        </div>
                        {{-- Доктора --}}
                        <div class="mb-4 mt-5">
                            <h2 class="fs-5 fw-semibold mb-3">Доктора</h2>

                            @php
                            $doctors = \App\Models\Doctor::where('clinic', $clinic->name)->get();
                            @endphp

                            <div class="row g-3">
                                @forelse ($doctors as $doctor)
                                <div class="col-md-6 col-lg-4 col-sm-6">
                                    <div class="card h-100 shadow-sm border-0 position-relative doctor-card">
                                        {{-- Лапка с рейтингом --}}
                                        <div class="rating-badge">
                                            <img width="24px" src="{{ asset('storage/icon/stars/doctors_stars.png') }}" alt="Рейтинг">
                                            <span class="rating-value">4.5</span>
                                        </div>
                                        <div class="card-body text-center">
                                            <img src="{{ $doctor->photo ? asset('/' . $doctor->photo) : asset('/doctors/default.webp') }}"
                                                alt="{{ $doctor->name }}"
                                                class="doctor-photo mb-3">
                                            <h5 class="card-title mb-1">{{ $doctor->name }}</h5>
                                            <p class="text-muted mb-2">{{ $doctor->specialization ?? 'Ветеринар' }}</p>
                                            @if($doctor->experience)
                                            <p class="small text-secondary">Опыт: {{ $doctor->experience }} лет</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <p class="text-muted">Доктора не указаны.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

<!-- 🖼️ Модальное окно просмотра фото -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-transparent border-0 shadow-none position-relative">
      <div class="modal-body p-0 text-center">
        <img id="modalPhoto" src="" alt="Фото отзыва" class="img-fluid rounded shadow-lg">
      </div>

      <!-- Кнопки навигации -->
      <button class="btn btn-light position-absolute top-50 start-0 translate-middle-y shadow"
              id="prevPhoto" style="opacity: 0.8;">❮</button>
      <button class="btn btn-light position-absolute top-50 end-0 translate-middle-y shadow"
              id="nextPhoto" style="opacity: 0.8;">❯</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
document.addEventListener('DOMContentLoaded', () => {
    /* ===================== 🔄 СОРТИРОВКА ===================== */
    const reviewList = document.getElementById('reviewList');
    const sortSelect = document.getElementById('sortReviews');

    if (reviewList && sortSelect) {
        sortSelect.addEventListener('change', () => {
            const sortType = sortSelect.value;
            const reviews = Array.from(reviewList.querySelectorAll('.review-card'));

            reviews.sort((a, b) => {
                const dateA = parseInt(a.dataset.date);
                const dateB = parseInt(b.dataset.date);
                const ratingA = parseInt(a.dataset.rating);
                const ratingB = parseInt(b.dataset.rating);

                switch (sortType) {
                    case 'date_asc': return dateA - dateB;
                    case 'date_desc': return dateB - dateA;
                    case 'rating_asc': return ratingA - ratingB;
                    case 'rating_desc': return ratingB - ratingA;
                    default: return 0;
                }
            });

            reviewList.innerHTML = '';
            reviews.forEach(r => reviewList.appendChild(r));
        });
    }


    if (editModal && editForm) {
        // 🌟 Установка рейтинга
        editStars.forEach(star => {
            star.addEventListener('click', () => {
                const value = star.getAttribute('data-value');
                ratingValue.value = value;
                editStars.forEach(s => {
                    s.src = s.dataset.value <= value
                        ? '/storage/icon/button/award-stars_active.svg'
                        : '/storage/icon/button/award-stars_disable.svg';
                });
            });
        });

        // 📋 Заполнение данных в модалке
        editModal.addEventListener('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            if (!button) return;

            const reviewId = button.dataset.id;
            const rating = button.dataset.rating || 0;
            const liked = button.dataset.liked || '';
            const disliked = button.dataset.disliked || '';
            const content = button.dataset.content || '';
            const petId = button.dataset.petId || '';

            editForm.action = `/reviews/${reviewId}`;
            ratingValue.value = rating;
            likedInput.value = liked;
            dislikedInput.value = disliked;
            contentInput.value = content;
            petSelect.value = petId;

            editStars.forEach(s => {
                s.src = s.dataset.value <= rating
                    ? '/storage/icon/button/award-stars_active.svg'
                    : '/storage/icon/button/award-stars_disable.svg';
            });
        });
    }

    /* ===================== ✅ ФИЛЬТР ТОЛЬКО ВЕРИФИЦИРОВАННЫЕ ===================== */
    const checkbox = document.getElementById('verifiedOnly');
    if (checkbox) {
        checkbox.addEventListener('change', () => {
            const reviews = document.querySelectorAll('.review-card');
            const showVerifiedOnly = checkbox.checked;

            reviews.forEach(review => {
                const verified = review.dataset.verified === "1";
                review.style.display = (showVerifiedOnly && !verified) ? 'none' : '';
            });
        });
    }

    /* ===================== 🖼️ ПРОСМОТР ФОТО ===================== */
    const photoModalEl = document.getElementById('photoModal');
    if (photoModalEl) {
        const photoModal = new bootstrap.Modal(photoModalEl);
        const modalImage = document.getElementById('modalPhoto');
        const prevBtn = document.getElementById('prevPhoto');
        const nextBtn = document.getElementById('nextPhoto');

        let currentPhotos = [];
        let currentIndex = 0;

        document.querySelectorAll('.review-photo').forEach(img => {
            img.addEventListener('click', () => {
                const reviewId = img.dataset.reviewId;
                const gallery = document.querySelectorAll(`.review-photos[data-review-id="${reviewId}"] .review-photo`);
                currentPhotos = Array.from(gallery).map(i => i.src);
                currentIndex = parseInt(img.dataset.index);

                showPhoto(currentIndex);
                photoModal.show();
            });
        });

        function showPhoto(index) {
            if (currentPhotos.length === 0) return;
            if (index < 0) index = currentPhotos.length - 1;
            if (index >= currentPhotos.length) index = 0;
            currentIndex = index;
            modalImage.src = currentPhotos[currentIndex];
        }

        prevBtn?.addEventListener('click', () => showPhoto(currentIndex - 1));
        nextBtn?.addEventListener('click', () => showPhoto(currentIndex + 1));

        document.addEventListener('keydown', e => {
            if (!photoModalEl.classList.contains('show')) return;
            if (e.key === 'ArrowLeft') showPhoto(currentIndex - 1);
            if (e.key === 'ArrowRight') showPhoto(currentIndex + 1);
        });
    }

    /* ===================== 🏅 СЛАЙДЕР НАГРАД ===================== */
    const carousel = document.getElementById('awardCarousel');
    if (carousel) {
        const bsCarousel = new bootstrap.Carousel(carousel);
        document.querySelectorAll('.award-thumb').forEach((thumb) => {
            thumb.addEventListener('click', (event) => {
                const index = parseInt(event.currentTarget.dataset.index);
                bsCarousel.to(index);
            });
        });
    }

    /* ===================== 🔗 ВКЛАДКИ ===================== */
    const hash = window.location.hash;
    if (hash) {
        const triggerEl = document.querySelector(`a[href="${hash}"]`);
        if (triggerEl) {
            const tab = new bootstrap.Tab(triggerEl);
            tab.show();
        }
    }

    document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(link => {
        link.addEventListener('shown.bs.tab', e => {
            history.replaceState(null, null, e.target.getAttribute('href'));
        });
    });

    /* ===================== 🐾 ПЛАВНАЯ ПРОКРУТКА ===================== */
    document.querySelectorAll('.paw-link').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const target = document.querySelector(link.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                target.classList.add('highlight-section');
                setTimeout(() => target.classList.remove('highlight-section'), 3000);
            }
        });
    });
    /* ===================== ✍️ ОТКРЫТИЕ / ЗАКРЫТИЕ ФОРМЫ ОТЗЫВА ===================== */
const toggleButton = document.getElementById('toggleReviewButton');
const reviewForm = document.getElementById('addReviewForm');

if (toggleButton && reviewForm) {
    toggleButton.addEventListener('click', () => {
        const collapse = new bootstrap.Collapse(reviewForm, {
            toggle: true
        });

        // Меняем текст кнопки при открытии / закрытии
        const isShown = reviewForm.classList.contains('show');
        toggleButton.textContent = isShown ? '✍️ Оставить отзыв' : '▲ Скрыть форму';
    });

    reviewForm.addEventListener('shown.bs.collapse', () => {
        toggleButton.textContent = '▲ Скрыть форму';
    });

    reviewForm.addEventListener('hidden.bs.collapse', () => {
        toggleButton.textContent = '✍️ Оставить отзыв';
    });
}

});

</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 🌟 Оценка в форме добавления отзыва
    const addStars = document.querySelectorAll('#addRatingStars .rating-star');
    const addRatingValue = document.getElementById('addRatingValue');

    if (addStars.length && addRatingValue) {
        addStars.forEach(star => {
            star.addEventListener('click', () => {
                const value = star.dataset.value;
                addRatingValue.value = value;

                addStars.forEach(s => {
                    s.src = s.dataset.value <= value
                        ? '/storage/icon/button/award-stars_active.svg'
                        : '/storage/icon/button/award-stars_disable.svg';
                });
            });
        });
    }
});
</script>

</body>
        </main>
        <footer class="footer-fullwidth mt-auto w-100">
            @include('layouts.footer')
        </footer>
</div>
@endsection
