<!-- Карточка одной отдельной записи -->

@php
use App\Models\Pet;
@endphp
@extends('layouts.app')

<!-- ВЫВОДИТЬ ГОРОД ИЗ КОТОРОГО ТА ИЛИ ИНАЯ ЗАПИСЬКА-----------------------------------
 --------------------------------
 ------------ ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- -
 ------------ ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- -
 ------------ ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- -
 ------------ ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- -
 ------------ ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- -
 ------------ ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- -
 ------------ ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- -
 ------------ ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- -
 ------------ ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- -
 ------------ ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- -
 ------------ ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- ------------- -->

@section('content')
<body>

<div class="d-flex flex-column min-vh-100 bg-white">
    @include('layouts.header')
    <main class="flex-grow-1 container py-5">
        <main class="flex-grow-1 container py-5">
            {{-- 🔙 Кнопка "В каталог" --}}
            <div class="mb-3">
                <a href="{{ route('clinics.index') }}" title="Вернутся к каталогу всех клиник города" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 shadow-sm back-to-catalog">
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
                        <div class="text-muted small mt-2 reviwes-avarege">Отзывов пока нет</div>
                        @endif
                    </div>



                    


            {{-- Вкладки  --}}
@php
    // Определяем активную вкладку через параметр в URL (?tab=reviews)
    $activeTab = request('tab', 'contacts');
@endphp

<ul class="nav nav-tabs mb-4" id="clinicTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'contacts' ? 'active' : '' }}"
           href="?tab=contacts"
           role="tab"
           title="Просмотреть контакты"
           aria-controls="contacts"
           aria-selected="{{ $activeTab === 'contacts' ? 'true' : 'false' }}">
            Контакты
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'services' ? 'active' : '' }}"
           href="?tab=services"
           role="tab"
           title="Открыть список услуг"
           aria-controls="services"
           aria-selected="{{ $activeTab === 'services' ? 'true' : 'false' }}">
            Услуги
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $activeTab === 'reviews' ? 'active' : '' }}"
           href="?tab=reviews"
           role="tab"
           title="Прочитать отзывы о клинике"
           aria-controls="reviews"
           aria-selected="{{ $activeTab === 'reviews' ? 'true' : 'false' }}">
            Отзывы
        </a>
    </li>
</ul>

{{-- Контент вкладок --}}

<div class="tab-content" id="clinicTabsContent">

    {{-- Контакты --}}
    <div class="tab-pane fade {{ $activeTab === 'contacts' ? 'show active' : '' }}" id="contacts" role="tabpanel">
                        {{-- Контакты --}}
                        
                            <div class="row">
                                {{-- Левая часть: контакты --}}
                                <div class="col-md-7">
                                    <div class="text-secondary mb-4">
                                        <div>📍 {{ $clinic->country }}, {{ $clinic->region }}, {{ $clinic->city }}, {{ $clinic->street }} {{ $clinic->house }}</div>
                                        <div>🕒 {{ $clinic->workdays }} — {{ $clinic->schedule }}</div>
                                        {{-- Телефоны как ссылки --}}
                                        @if($clinic->phone1)
                                        <div>
                                            📞 <a href="tel:{{ preg_replace('/\D/', '', $clinic->phone1) }}">{{ $clinic->phone1 }}<img width="24px" src="{{ asset('storage/icon/contacts/phone.svg') }}" alt="Телефон" title="Позвонить"> </a>
                                            @if($clinic->phone2)
                                            , <a href="tel:{{ preg_replace('/\D/', '', $clinic->phone2) }}">{{ $clinic->phone2 }}<img width="24px" src="{{ asset('storage/icon/contacts/phone.svg') }}" alt="Телефон запасной" title="Позвонить"> </a>
                                            @endif
                                        </div>
                                        @endif
                                        <div>✉️ {{ $clinic->email }}</div>
                                        @if($clinic->telegram)
                                        <div>💬 Telegram: <a href="https://t.me/{{ $clinic->telegram }}" target="_blank">https://t.me/{{ $clinic->telegram }}<img width="24px" src="{{ asset('storage/icon/contacts/telegram.svg') }}" title="Связатся через телеграмм" alt="Телеграмм"></a></div>
                                        @endif
                                        @if($clinic->whatsapp)
                                        <div>💬 WhatsApp: <a href="https://wa.me/{{ $clinic->whatsapp }}" target="_blank">{{ $clinic->whatsapp }}<img width="24px" src="{{ asset('storage/icon/contacts/whatsapp.svg') }}" title="Связаться через Вотсапп" alt="Вотсапп"></a></div>
                                        @endif
                                        @if($clinic->website)
                                        <div>💬 <a href="{{ $clinic->website }}" target="_blank" title="Перейти на сайт клиники">Перейти на сайт</a></div>
                                        @endif
                                         @if($clinic->description)
                                         <br>
                                         <label class="label_description_clinic" for="description">О клинике</label>
                                        <div id="#description">{{ $clinic->description }}</div>
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
    <div class="tab-pane fade {{ $activeTab === 'services' ? 'show active' : '' }}" id="services" role="tabpanel">
        <h4>Услуги</h4>

                        {{-- Услуги --}}
                        
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




    {{-- Отзывы --}}
    <div class="tab-pane fade {{ $activeTab === 'reviews' ? 'show active' : '' }}" id="reviews" role="tabpanel">


                        {{-- Отзывы --}}
                        
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
{{-- ✍️ Кнопка "Оставить отзыв" --}}
<button id="toggleReviewButton"
        class="btn btn-primary mb-3"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#openReviewForm"
        aria-expanded="false"
        aria-controls="openReviewForm">
    ✍️ Оставить отзыв
</button>
</div>

{{-- 🔽 Скрытая форма --}}
<div class="collapse" id="openReviewForm">
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
                <input type="hidden" name="redirect_slug" value="{{ $clinic->slug }}">
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
        <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}"
       title="Нажмите чтобы авторизоваться"
       class="login_link">
    
    войдите в аккаунт</a>.
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
                                            <a href="{{ route('user.profile', $review->user->id) }}" title="Перейти к профилю пользователя" class="fw-semibold text-decoration-none text-primary">
                                                {{ $review->user->name }}
                                            </a>
                                            <div class="small text-muted">{{ $review->review_date->format('d.m.Y') }}</div>
                                            @if(Auth::id() === $review->user_id)
                                            {{-- Отметка "Реальный клиент" --}}
                                            @if($review->receipt_verified == 1)
                                            <span class="verifed_client">
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
    <div class="tab-pane fade {{ $activeTab === 'awards' ? 'show active' : '' }}" id="awards" role="tabpanel">
        <h4>Награды</h4>
{{-- Награды --}}
    <div class="row g-4">
        @forelse($clinic->awards ?? [] as $index => $award)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card h-100 shadow-sm border-0">
                    <a href="#" 
                    title="Просмотреть награду подробнее"
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
    @endif
</div>
    </div>
</div>

{{-- Доктора --}}
                        <div class="mb-4 mt-5">
                            <h2 class="fs-5 fw-semibold mb-3">Доктора</h2>

                            @php
                            $doctors = \App\Models\Doctor::where('clinic_id', $clinic->id)->get();
                            @endphp

                            <div class="row g-3">
                                @forelse ($doctors as $doctor)
                                @php
    $doctorReviews = $doctor->reviews ?? collect();
    $doctorAvgRating = $doctorReviews->avg('rating')
        ? number_format($doctorReviews->avg('rating'), 1)
        : '0.0';
@endphp

                                <div class="col-md-6 col-lg-4 col-sm-6">
                                                    <a href="{{ route('doctors.show', $doctor->slug) }}" title="Перейти в профиль доктора" class="text-decoration-none text-reset">
                                    <div class="card h-100 shadow-sm border-0 position-relative doctor-card">
                                        {{-- Лапка с рейтингом --}}
                                        <div class="rating-badge">
                                            <img width="24px" src="{{ asset('storage/icon/stars/doctors_stars.png') }}" alt="Рейтинг">
                                            <span class="rating-value">{{ $doctorAvgRating }}</span>

                                        </div>
                                        <div class="card-body text-center">
                                            <img src="{{ $doctor->photo ? asset('/storage/' . $doctor->photo) : asset('/storage/doctors/default-doctor.webp') }}"
                                                alt="{{ $doctor->name }}"
                                                class="doctor-photo mb-3">
                                            <h5 class="card-title mb-1">{{ $doctor->name }}</h5>
                                            <p class="text-muted mb-2">{{ $doctor->specialization ?? 'Ветеринар' }}</p>
                                            @if($doctor->experience)
                                            <p class="small text-secondary">Опыт: {{ $doctor->experience }} лет</p>
                                            @endif
                                        </div>
                                    </div>
                                </a>
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
</main>
<footer class="footer-fullwidth mt-auto w-100">
            </body>
            @include('layouts.footer')
        </footer>
</div>
@endsection