@extends('layouts.app')
        use App\Models\Review;

@section('title', $clinic->name)

@section('content')
<div class="d-flex flex-column min-vh-100 bg-white">

    @include('layouts.header')
    
    <main class="flex-grow-1 container py-5">
    <main class="flex-grow-1 container py-5">
    {{-- 🔙 Кнопка "В каталог" --}}
    <div class="mb-3">
        <a href="{{ route('pages.clinics.index') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 shadow-sm back-to-catalog">
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
                        <button class="nav-link active" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts" type="button" role="tab">Контакты</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" type="button" role="tab">Услуги</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="directions-tab" data-bs-toggle="tab" data-bs-target="#directions" type="button" role="tab">Отзывы</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="directions-tab" data-bs-toggle="tab" data-bs-target="#awards" type="button" role="tab">Награды</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="photos-tab" data-bs-toggle="tab" data-bs-target="#photos" type="button" role="tab">Фото</button>
                    </li>
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
                                        <div>💬  <a href="{{ $clinic->website }}">Перейти на сайт</a></div>
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
        loading="lazy"
    ></iframe>
</div>


    {{-- Дополнительная информация --}}
    <div class="text-muted small">
        <!-- <p><strong>Адрес:</strong> {{ $clinic->country }}, {{ $clinic->region }}, {{ $clinic->city }}, {{ $clinic->street }} {{ $clinic->house }}</p> -->
        @if(!empty($clinic->founded))
            <!-- <p><strong>Основана:</strong> {{ $clinic->founded }}</p> -->
        @endif
        @if(!empty($clinic->description))
            <!-- <p><strong>Описание:</strong> {{ $clinic->description }}</p> -->
        @endif
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

{{-- 🪄 Плавная прокрутка и подсветка --}}
<script>
document.querySelectorAll('.paw-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });

            // Подсветка блока на 3 секунды
            target.classList.add('highlight-section');
            setTimeout(() => {
                target.classList.remove('highlight-section');
            }, 3000);
        }
    });
});
</script>

<style>
/* 🐾 Алфавит */
.paw-link {
    display: inline-block;
    position: relative;
    text-align: center;
}
.paw-circle {
    position: relative;
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background-color: #f8f9fa;
    border: 2px solid #dee2e6;
    display: flex;
    border: none;
    align-items: center;
    justify-content: center;
    transition: transform 0.25s ease, background-color 0.25s;
    cursor: pointer;
}
.paw-circle:hover {
    background-color: #4787ff36;
    transform: scale(1.1);
    border-color: #ffb347;
        opacity: 0.95;
}
.paw-icon {
    position: absolute;
    width: 38px;
    height: 38px;
    opacity: 0.75;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
.paw-letter {
    position: relative;
    font-weight: 700;
    font-size: 1.1rem;
    color: #333;
}

/* 🔦 Подсветка блока при переходе */
.highlight-section {
    animation: highlightFade 3s ease-in-out;
}
@keyframes highlightFade {
    0%   { background-color: #cfffcdff; }
    100% { background-color: transparent; }
}

.specialization-block {
    scroll-margin-top: 120px;
}
</style>





{{-- Отзывы --}}
<div class="tab-pane fade" id="directions" role="tabpanel">
    @php

        $reviews = Review::where('reviewable_id', $clinic->id)
            ->where('reviewable_type', \App\Models\Clinic::class)
            ->with('user')
            ->latest('review_date')
            ->get();
    @endphp

    @if($reviews->isNotEmpty())
        <div class="list-group">
            @foreach($reviews as $review)
                <div class="list-group-item mb-3 border rounded shadow-sm p-4 review-card">

                    {{-- 🧍 Пользователь --}}
                    <div class="d-flex align-items-center mb-3">
@php
    $avatarPath = null;

    if (!empty($review->user->avatar)) {
        // Если у пользователя есть загруженный аватар в storage
        $avatarPath = asset('storage/' . $review->user->avatar);
    } elseif (!empty($review->user->photo)) {
        // Альтернативное поле (если используется 'photo')
        $avatarPath = asset('storage/' . $review->user->photo);
    } elseif (!empty($review->user->profile_photo_path)) {
        // Для пользователей Jetstream / Breeze
        $avatarPath = asset('storage/' . $review->user->profile_photo_path);
    } else {
        // Стандартный аватар
        $avatarPath = asset('storage/avatars/default/default_avatar.webp');
    }
@endphp

<img src="{{ $avatarPath }}" 
     alt="{{ $review->user->name }}" 
     class="rounded-circle me-3 border" 
     width="56" height="56">

                        <div>
                            <a href="{{ route('user.profile', $review->user->id) }}" class="fw-semibold text-decoration-none text-primary">
                                {{ $review->user->name }}
                            </a>
                            <div class="small text-muted">{{ $review->review_date->format('d.m.Y') }}</div>
                        </div>
                    </div>

                    {{-- ⭐ Оценка --}}
                    <div class="mb-3">
                        @for ($i = 1; $i <= 5; $i++)
                            <img src="{{ asset('storage/icon/button/' . ($i <= $review->rating ? 'award-stars_active.svg' : 'award-stars_disable.svg')) }}"
                                 width="20" alt="звезда">
                        @endfor
                    </div>

                    {{-- 👍 Понравилось --}}
                    @if($review->liked)
                        <div class="mb-2">
                            <strong class="text-success">Понравилось:</strong>
                            <p class="mb-1">{{ $review->liked }}</p>
                        </div>
                    @endif

                    {{-- 👎 Не понравилось --}}
                    @if($review->disliked)
                        <div class="mb-2">
                            <strong class="text-danger">Не понравилось:</strong>
                            <p class="mb-1">{{ $review->disliked }}</p>
                        </div>
                    @endif

                    {{-- 💬 Отзыв --}}
                    @if($review->content)
                        <div class="mb-3">
                            <strong>Отзыв:</strong>
                            <p class="mb-0">{{ $review->content }}</p>
                        </div>
                    @endif

                    {{-- 🐾 Данные о питомце --}}
                    <div class="small text-muted mt-2">
                        <em>Питомец:</em> {{ $review->pet_name }},
                        {{ $review->pet_type }},
                        {{ $review->pet_age }} лет
                    </div>

                    {{-- 📎 Фото из отзыва --}}
                    @if($review->photos && $review->photos->count())
                        <div class="mt-3 d-flex flex-wrap gap-2">
                            @foreach($review->photos as $photo)
                                <img src="{{ asset('storage/' . $photo->path) }}"
                                     alt="Фото из отзыва"
                                     class="rounded border"
                                     style="width: 120px; height: 120px; object-fit: cover;">
                            @endforeach
                        </div>
                    @endif

                    {{-- ✅ Проверка чека --}}
                    @if($review->receipt_verified)
                        <div class="mt-3 small text-success fw-semibold">
                            ✅ Отзыв подтверждён по чеку
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    @else
        <p class="text-muted">Отзывов пока нет.</p>
    @endif
</div>


                    {{-- Фото --}}
                    <div class="tab-pane fade" id="photos" role="tabpanel">
                        <div class="row g-3">
                            @if(!empty($clinic->photos))
                                @foreach($clinic->photos as $photo)
                                    <div class="col-md-4 col-sm-6">
                                        <img src="{{ asset('/' . $photo) }}" class="img-fluid rounded shadow-sm" alt="Фото клиники">
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">Фотографии пока не добавлены.</p>
                            @endif
                        </div>
                    </div>
                </div>
                    {{-- Награды --}}
                    <div class="tab-pane fade" id="awards" role="tabpanel">
                        <div class="row g-3">
                            @if(!empty($clinic->photos))
                                @foreach($clinic->photos as $photo)
                                    <div class="col-md-4 col-sm-6">
                                        <img src="{{ asset('/' . $photo) }}" class="img-fluid rounded shadow-sm" alt="Фото клиники">
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">Награды пока не добавлены.</p>
                            @endif
                        </div>
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
    </main>

    <footer class="footer-fullwidth mt-auto w-100">
        @include('layouts.footer')
    </footer>
</div>

<style>
.logo_clinic_card {
    width: 96px;
    height: 96px;
    object-fit: contain;
    border-radius: 8px;
    background-color: #f8f9fa;
    padding: 6px;
    border: 1px solid #eee;
}

.doctor-photo {
    width: 130px;
    height: 180px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #ddd;
    background-color: #f8f9fa;
}

.nav-tabs .nav-link {
    color: #555;
    font-weight: 500;
}
.nav-tabs .nav-link.active {
    color: #000;
    border-color: #dee2e6 #dee2e6 #fff;
}

.footer-fullwidth {
    background-color: #f8f9fa;
    border-top: 1px solid #e5e5e5;
}

.doctor-card {
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.doctor-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
}

/* Лапка с рейтингом — в левом верхнем углу */
.rating-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #fff;
    border-radius: 20px;
    padding: 4px 8px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #ff7b00;
    box-shadow: 0 1px 4px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 4px;
}
.rating-badge .rating-value {
    font-size: 0.9rem;
}

.nav-item{
    background-color: #c9c1f72d;
    border: 1px solid #adadad1f;
}
.specialization_block{
    padding-top: 1%;
    padding-left: 1%;
}

/* 🔙 Кнопка "В каталог" */
.back-to-catalog {
    height: 30px;
    font-weight: 600;
    border-radius: 8px;
    padding: 3px 3px;
    transition: all 0.25s ease-in-out;
    border: none;
    color: #4b4b4bff;
    background-color: #abc2ae11;
}

.back-to-catalog:hover {
    background-color: #0000000c;
    color: #000000ff !important;
    transform: translateY(-2px);
}


.back-to-catalog img {
    opacity: 0.85;
    transition: transform 0.3s;
}

.back-to-catalog:hover img {
    transform: rotate(-10deg);
}

.py-5 {
    padding-top: 1.5rem !important;
    padding-bottom: 3rem !important;
}

.mb-3 {
    margin-bottom: 1.5rem !important;
}
.rating-summary img {
    margin-right: 2px;
}
.rating-summary {
    background-color: #fff7e6;
    border: 1px solid #ffe0b3;
    border-radius: 10px;
    padding: 4px 10px;
    display: inline-flex;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}


</style>
@endsection