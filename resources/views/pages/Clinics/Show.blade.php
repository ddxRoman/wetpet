@php
use App\Models\Pet;
@endphp
@extends('layouts.app')
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
                                        target.scrollIntoView({
                                            behavior: 'smooth',
                                            block: 'start'
                                        });

                                        // Подсветка блока на 3 секунды
                                        target.classList.add('highlight-section');
                                        setTimeout(() => {
                                            target.classList.remove('highlight-section');
                                        }, 3000);
                                    }
                                });
                            });
                        </script>

                        {{-- Отзывы --}}
                        <div class="tab-pane fade" id="directions" role="tabpanel">
                            @php


                            $reviews = Review::where('reviewable_id', $clinic->id)
                            ->where('reviewable_type', \App\Models\Clinic::class)
                            ->with(['user', 'photos'])
                            ->latest('review_date')
                            ->get();

                            $pets = Pet::where('user_id', auth()->id())->get();
                            @endphp

                            {{-- 📝 Кнопка открытия формы --}}
{{-- 📝 Кнопка открытия / закрытия формы --}}
@auth
    <div class="text-end mb-3">
        <button id="toggleReviewButton" class="btn btn-outline-primary" type="button">
            ✍️ Оставить отзыв
        </button>
    </div>



                            {{-- 🔽 Скрытая форма --}}
                            <div class="collapse" id="addReviewForm">
                                <div class="card shadow-sm border-0 mb-4">
                                    <div class="card-body">
                                        <h5 class="fw-semibold mb-3">Оставить отзыв</h5>
                                        <form id="reviewForm" method="POST" action="{{ route('reviews.store') }}" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="reviewable_id" value="{{ $clinic->id }}">
                                            <input type="hidden" name="reviewable_type" value="{{ (\App\Models\Clinic::class) }}">

                                            {{-- ⭐ Оценка --}}
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Оценка:</label>
                                                <div id="ratingStars" class="d-flex gap-1">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <img src="{{ asset('storage/icon/button/award-stars_disable.svg') }}"
                                                        data-value="{{ $i }}"
                                                        class="rating-star"
                                                        width="28"
                                                        alt="звезда">
                                                        @endfor
                                                </div>
                                                <input type="hidden" name="rating" id="ratingValue" value="0">
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
                                                <textarea name="content" id="reviewText" class="form-control small-textarea" placeholder="Напишите свой отзыв..." rows="2"></textarea>
                                            </div>

                                            {{-- 🐾 Питомец --}}
                                            <div class="mb-3">
                                                <label class="form-label">Ваш питомец:</label>
                                                <select name="pet_id" class="form-select">
                                                    @forelse($pets as $pet)
                                                    <option value="{{ $pet->id }}">{{ $pet->name }} — {{ $pet->type }}</option>
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
                            <p class="text-muted mb-4">Чтобы оставить отзыв, <a href="{{ route('login') }}">войдите в аккаунт</a>.</p>
                            @endauth

{{-- 🔽 Панель сортировки + фильтр --}}
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 bg-light p-3 rounded shadow-sm">
    <div class="d-flex align-items-center gap-2">
        <label for="sort" class="fw-semibold text-secondary mb-0">Сортировать по:</label>
        <select id="sortReviews" class="form-select form-select-sm" style="width: 180px;">
            <option value="date_desc">Дате ↓ (новые)</option>
            <option value="date_asc">Дате ↑ (старые)</option>
            <option value="rating_desc">Оценке ↓ (высокие)</option>
            <option value="rating_asc">Оценке ↑ (низкие)</option>
        </select>
    </div>
</div>


{{-- 🔽 Список отзывов --}}
<div id="reviewList" class="list-group">
    @foreach($reviews as $review)
<div class="list-group-item mb-3 border rounded shadow-sm p-4 review-card"
     data-date="{{ $review->review_date->timestamp }}"
     data-rating="{{ $review->rating }}"
  data-verified="{{ $review->receipt_verified }}">



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
@if($review->receipt_verified=1)
    <span class="verifed_client">

       ✅ Реальный клиент
    </span>
@endif
    <div class="mt-1">
        <button class="btn btn-sm btn-outline-secondary edit-review" data-id="{{ $review->id }}">Редактировать</button>
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
                <div><strong class="text-success">Понравилось:</strong> {{ $review->liked }}</div>
            @endif
            @if($review->disliked)
                <div><strong class="text-danger">Не понравилось:</strong> {{ $review->disliked }}</div>
            @endif
            @if($review->content)
                <p class="mt-2">{{ $review->content }}</p>
            @endif

            <div class="small text-muted mt-2">
                <em>Питомец:</em> {{ $review->pet_name }} ({{ $review->pet_type }})
            </div>

            {{-- Фото отзыва --}}
            @if($review->photos && $review->photos->count())
                <div class="mt-3 d-flex flex-wrap gap-2">
                    @foreach($review->photos as $photo)
                        <img src="{{ asset('storage/' . $photo->path) }}"
                             class="rounded border"
                             style="width: 100px; height: 100px; object-fit: cover;">
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>

{{-- 🚀 JS сортировка без перезагрузки --}}
{{-- 🚀 JS сортировка и фильтр отзывов --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('sortReviews');
    const list = document.getElementById('reviewList');
    const verifiedOnly = document.getElementById('verifiedOnly');

    if (!list) return;

    function normalizeVerified(value) {
        // Приводим любое значение в data-verified к булевому
        if (!value) return false;
        value = value.toString().toLowerCase().trim();
        return value === '1' || value === 'true' || value === 'yes';
    }

    function applyFilters() {
        const value = select?.value || 'date_desc';
        const items = Array.from(list.querySelectorAll('.review-card'));

        // Фильтрация
        let filtered = items.filter(item => {
            const verified = normalizeVerified(item.dataset.verified);
            return verifiedOnly?.checked ? verified : true;
        });

        // Сортировка
        filtered.sort((a, b) => {
            const aDate = Number(a.dataset.date);
            const bDate = Number(b.dataset.date);
            const aRating = Number(a.dataset.rating);
            const bRating = Number(b.dataset.rating);

            switch (value) {
                case 'date_asc': return aDate - bDate;
                case 'date_desc': return bDate - aDate;
                case 'rating_asc': return aRating - bRating;
                case 'rating_desc': return bRating - aRating;
                default: return 0;
            }
        });

        // Перестроение DOM
        list.innerHTML = '';
        filtered.forEach(item => list.appendChild(item));
    }

    // Обработчики событий
    select?.addEventListener('change', applyFilters);
    verifiedOnly?.addEventListener('change', applyFilters);

    // Инициализация при загрузке
    applyFilters();
});
</script>



                        {{-- ⚡ JS: активация звёзд и textarea --}}
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                const stars = document.querySelectorAll('.rating-star');
                                const ratingInput = document.getElementById('ratingValue');
                                const textarea = document.getElementById('reviewText');

                                // ⭐ Система звёзд
                                stars.forEach(star => {
                                    star.addEventListener('click', () => {
                                        const value = star.dataset.value;
                                        ratingInput.value = value;
                                        stars.forEach(s => {
                                            s.src = s.dataset.value <= value ?
                                                "{{ asset('storage/icon/button/award-stars_active.svg') }}" :
                                                "{{ asset('storage/icon/button/award-stars_disable.svg') }}";
                                        });
                                    });
                                });

                                // ✨ Анимация textarea
                                if (textarea) {
                                    textarea.addEventListener('focus', () => textarea.classList.add('expanded'));
                                    textarea.addEventListener('blur', () => {
                                        if (!textarea.value.trim()) textarea.classList.remove('expanded');
                                    });
                                }
                            });
                            const toggleBtn = document.getElementById('toggleReviewForm');
                            const formContainer = document.getElementById('reviewFormContainer');
                            if (toggleBtn && formContainer) {
                                toggleBtn.addEventListener('click', () => {
                                    formContainer.classList.toggle('d-none');
                                    toggleBtn.textContent = formContainer.classList.contains('d-none') ?
                                        '✍️ Оставить отзыв' :
                                        '🔽 Скрыть форму';
                                });
                            }

                            const toggleButton = document.getElementById('toggleReviewButton');
const form = document.getElementById('addReviewForm');

if (toggleButton && form) {
    toggleButton.addEventListener('click', () => {
        const collapse = new bootstrap.Collapse(form, { toggle: false });
        if (form.classList.contains('show')) {
            collapse.hide();
        } else {
            collapse.show();
        }
    });
}


// ✏️ Редактирование отзыва
document.addEventListener('DOMContentLoaded', () => {
    const editButtons = document.querySelectorAll('.edit-review');

    editButtons.forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            const card = btn.closest('.review-card');
            const content = card.querySelector('p')?.textContent.trim() || '';

            const newText = prompt('Измените текст отзыва:', content);
            if (newText === null) return;

            const response = await fetch(`/reviews/${id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ content: newText }),
            });

            if (response.ok) {
                card.querySelector('p').textContent = newText;
                alert('Отзыв обновлён!');
            } else {
                alert('Ошибка при обновлении.');
            }
        });
    });
});



                        </script>

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
@endsection