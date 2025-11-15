{{-- Кнопка открытия/закрытия аккордеона --}}
<button id="toggleAccordionBtn"
        class="btn btn-primary mb-3"
        type="button"
        aria-expanded="false"
        aria-controls="accordionContent">
    ✍️ Оставить отзыв
</button>

{{-- Аккордеон (скрытая секция) --}}
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


{{-- JS: управление (Bootstrap + fallback) --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('toggleAccordionBtn');
    const closeBtn = document.getElementById('closeAccordionBtn');
    const content = document.getElementById('accordionContent');

    if (!btn || !content) return;

    // Функция обновления aria и текста кнопки
    function updateButton(isOpen) {
        btn.setAttribute('aria-expanded', String(Boolean(isOpen)));
        btn.textContent = isOpen ? '✖️ Свернуть форму' : '✍️ Оставить отзыв';
    }

    // Если Bootstrap доступен — используем collapse API (без автоматического toggle при инициации)
    if (window.bootstrap && bootstrap.Collapse) {
        // создаём экземпляр, но не трогаем состояние на создании (toggle: false)
        const bsCollapse = new bootstrap.Collapse(content, { toggle: false });

        // Обработка клика по основной кнопке — переключаем состояние
        btn.addEventListener('click', () => {
            // если открыт — скрываем, если скрыт — показываем
            if (content.classList.contains('show')) {
                bsCollapse.hide();
            } else {
                bsCollapse.show();
            }
        });

        // Вешаем слушатели событий Bootstrap, чтобы обновлять текст/aria
        content.addEventListener('shown.bs.collapse', () => updateButton(true));
        content.addEventListener('hidden.bs.collapse', () => updateButton(false));

        // Внутренняя кнопка закрытия
        closeBtn?.addEventListener('click', () => bsCollapse.hide());

        // Инициализация текста в зависимости от текущего состояния (на случай SSR / server-rendered)
        updateButton(content.classList.contains('show'));
        return;
    }

    closeBtn?.addEventListener('click', () => {
        content.classList.remove('show');
        content.style.display = 'none';
        updateButton(false);
    });

    // Стартовое состояние: если блок уже видим в DOM — отобразим соответствующий текст
    const initiallyOpen = content.classList.contains('show') || (getComputedStyle(content).display !== 'none' && getComputedStyle(content).display !== 'none');
    updateButton(initiallyOpen);
});
</script>

{{-- Доп. стили если хочешь плавность (необязательно) --}}
<style>
/* Простая плавность для fallback-режима */
#accordionContent {
    transition: max-height 230ms ease, opacity 180ms ease;
    overflow: hidden;
}
#accordionContent.collapse:not(.show) {
    display: none;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
