 {{-- Отзывы --}}
    <div class="tab-pane fade {{ $tab === 'reviews' ? 'show active' : '' }}" id="reviews" role="tabpanel">

                        {{-- Отзывы --}}
                        
@php
    // Было: Review::where(...)
    $reviews = \App\Models\Review::where('reviewable_id', $clinic->id)
        ->where('reviewable_type', \App\Models\Clinic::class)
        ->with(['user', 'photos'])
        ->latest('review_date')
        ->get();

    $pets = \App\Models\Pet::where('user_id', auth()->id()) // Также добавьте \App\Models\ для Pet на всякий случай
        ->with('animal')
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
$reviews = \App\Models\Review::where('reviewable_id', $clinic->id)
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

