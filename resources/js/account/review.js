document.addEventListener('DOMContentLoaded', () => {

/* =========================================================
   🔹 ОБЩИЕ ПЕРЕМЕННЫЕ
========================================================= */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

/* =========================================================
   🔹 ЗАГРУЗКА И УПРАВЛЕНИЕ ОТЗЫВАМИ
========================================================= */
const reviewsList = document.getElementById('reviews-list');
const tabBtn = document.querySelector('[data-tab="reviews"]');
let loaded = false;
let currentReviewsPage = 1;

async function loadReviews(page = 1) {
    if (!reviewsList) return;

    try {
        const res = await fetch(`/account/reviews?page=${page}`, { credentials: 'same-origin' });
        if (!res.ok) throw new Error(await res.text());
        const payload = await res.json();

        const data = payload.data ?? [];
        currentReviewsPage = payload.current_page ?? 1;

        // Удалили последний отзыв на странице — переходим на предыдущую
        if (!data.length && currentReviewsPage > 1) {
            return loadReviews(currentReviewsPage - 1);
        }
        const lastPage = payload.last_page ?? 1;

        reviewsList.innerHTML = data.length
            ? data.map(renderCard).join('')
            : '<p class="empty-message">Вы пока не оставили ни одного отзыва.</p>';

        renderPagination(currentReviewsPage, lastPage);

        reviewsList.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (e) {
        console.error(e);
        reviewsList.innerHTML =
            '<p class="empty-message" style="color:red;">Ошибка при загрузке отзывов.</p>';
        removePagination();
    }
}

// Строит правильную ссылку на объект отзыва в зависимости от его типа.
// У клиник и организаций роут двухсегментный (нужен слаг города),
// у врачей и специалистов — односегментный.
function buildTargetUrl(r) {
    const identifier = r.target_slug || r.target_id;

    switch (r.target_type) {
        case 'Clinic':
            return `/clinics/${r.target_city_slug ?? ''}/${identifier}`;
        case 'Organization':
            return `/organizations/${r.target_city_slug ?? ''}/${identifier}`;
        case 'Specialist':
            return `/specialists/${identifier}`;
        case 'Doctor':
        default:
            return `/doctors/${identifier}`;
    }
}

// Данные отзыва для формы редактирования (resources/js/review-manage.js)
function reviewPayload(r) {
    return {
        id: r.id,
        rating: r.rating,
        liked: r.liked,
        disliked: r.disliked,
        content: r.content,
        photos: (r.photos || []).map(p => ({ id: p.id, url: `/storage/${p.photo_path}` })),
        receipts: (r.receipts || []).map(f => ({ id: f.id, name: String(f.receipt_path || '').split('/').pop() })),
    };
}

function renderCard(r) {
    const isClinic = r.target_type === 'Clinic';

    const address = isClinic
        ? [r.region, r.city, r.street, r.house]
            .filter(Boolean)
            .map(escapeHtml)
            .join(', ')
        : '';

    return `
<article class="review-card"
    data-id="${r.id}"
    data-date="${new Date(r.created_at).getTime()}"
    data-rating="${r.rating ?? 0}">

    <header class="review-header">
        <div class="clinic-info-block">
            <a href="${buildTargetUrl(r)}" 
               title="Открыть"
               class="clinic-name">
               ${escapeHtml(r.target_name)}
            </a>
            ${address ? `<div class="clinic-address">${address}</div>` : ''}
        </div>
        <time>${new Date(r.created_at).toLocaleDateString()}</time>
    </header>

    <div class="review-content">${escapeHtml(r.content || '')}</div>

    <div class="review-liked"><strong>Понравилось:</strong> ${escapeHtml(r.liked || '')}</div>
    <div class="review-disliked"><strong>Не понравилось:</strong> ${escapeHtml(r.disliked || '')}</div>

    <footer class="review-footer">
        <div class="review-rating">Оценка: ${r.rating ?? 0}</div>
        <div class="review-actions d-flex gap-2 mt-2">
            <button type="button" class="btn btn-sm btn-outline-primary"
                    data-review-edit
                    data-review="${escapeHtml(JSON.stringify(reviewPayload(r)))}">✏️ Редактировать</button>
            <button type="button" class="btn btn-sm btn-outline-danger"
                    data-review-delete="${r.id}">Удалить</button>
        </div>
    </footer>
</article>`;
}

/* =========================================================
   🔹 ПАГИНАЦИЯ (по 20 отзывов на странице)
========================================================= */
function removePagination() {
    document.getElementById('reviews-pagination')?.remove();
}

function renderPagination(current, last) {
    removePagination();
    if (!reviewsList || last <= 1) return;

    const nav = document.createElement('div');
    nav.id = 'reviews-pagination';
    nav.className = 'd-flex align-items-center justify-content-center gap-2 mt-3';
    nav.innerHTML = `
        <button type="button" class="btn btn-outline-secondary btn-sm" data-page="${current - 1}" ${current <= 1 ? 'disabled' : ''}>← Назад</button>
        <span class="small text-muted">Страница ${current} из ${last}</span>
        <button type="button" class="btn btn-outline-secondary btn-sm" data-page="${current + 1}" ${current >= last ? 'disabled' : ''}>Вперёд →</button>
    `;

    nav.querySelectorAll('button[data-page]').forEach(btn => {
        btn.addEventListener('click', () => {
            const page = parseInt(btn.dataset.page, 10);
            if (page >= 1 && page <= last) loadReviews(page);
        });
    });

    reviewsList.insertAdjacentElement('afterend', nav);
}

/* =========================================================
   🔹 ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
========================================================= */
function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

/* =========================================================
   🔹 ТАБ "ОТЗЫВЫ"
========================================================= */
// После редактирования/удаления отзыва обновляем список без перезагрузки страницы
if (reviewsList) {
    document.addEventListener('review:changed', e => {
        e.preventDefault();
        loadReviews(currentReviewsPage);
    });
}

tabBtn?.addEventListener('click', () => {
    if (!loaded) {
        loadReviews();
        loaded = true;
    }
});

if (location.hash === '#reviews') {
    loadReviews();
    loaded = true;
}

/* =========================================================
   🔹 ФИЛЬТР "РЕАЛЬНЫЕ КЛИЕНТЫ"
========================================================= */
const verifiedCheckbox = document.getElementById('verifiedOnly');
if (verifiedCheckbox) {
    const applyFilter = () => {
        document.querySelectorAll('.review-card').forEach(card => {
            const hasBadge = !!card.querySelector('.verified-badge');
            card.style.display =
                (verifiedCheckbox.checked && !hasBadge) ? 'none' : '';
        });
    };

    verifiedCheckbox.addEventListener('change', applyFilter);
    if (verifiedCheckbox.checked) applyFilter();
}

/* =========================================================
   🔹 СОРТИРОВКА
========================================================= */
const reviewList = document.getElementById('reviewList');
const sortSelect = document.getElementById('sortReviews');

if (reviewList && sortSelect) {
    sortSelect.addEventListener('change', () => {
        const reviews = Array.from(reviewList.querySelectorAll('.review-card'));

        reviews.sort((a, b) => {
            const da = +a.dataset.date;
            const db = +b.dataset.date;
            const ra = +a.dataset.rating;
            const rb = +b.dataset.rating;

            switch (sortSelect.value) {
                case 'date_asc': return da - db;
                case 'date_desc': return db - da;
                case 'rating_asc': return ra - rb;
                case 'rating_desc': return rb - ra;
                default: return 0;
            }
        });

        reviewList.innerHTML = '';
        reviews.forEach(r => reviewList.appendChild(r));
    });

    if (sortSelect.value === 'date_desc') {
        sortSelect.dispatchEvent(new Event('change'));
    }
}

/* =========================================================
   🔹 МОДАЛКА ФОТО + ЛИСТАНИЕ
========================================================= */
const modal = document.getElementById('photoModal');
const modalImg = document.getElementById('modalPhoto');
const prevBtn = document.getElementById('prevPhoto');
const nextBtn = document.getElementById('nextPhoto');

let currentIndex = 0;
let currentPhotos = [];

document.querySelectorAll('.review-photo').forEach(img => {
    img.addEventListener('click', () => {
        currentIndex = +img.dataset.index;
        currentPhotos = Array.from(
            document.querySelectorAll(
                `.review-photos[data-review-id="${img.dataset.reviewId}"] .review-photo`
            )
        );
        modalImg.src = img.src;
        new bootstrap.Modal(modal).show();
    });
});

nextBtn?.addEventListener('click', () => {
    currentIndex = (currentIndex + 1) % currentPhotos.length;
    modalImg.src = currentPhotos[currentIndex].src;
});

prevBtn?.addEventListener('click', () => {
    currentIndex = (currentIndex - 1 + currentPhotos.length) % currentPhotos.length;
    modalImg.src = currentPhotos[currentIndex].src;
});

/* =========================================================
   🔹 АККОРДЕОН "ОСТАВИТЬ ОТЗЫВ"
========================================================= */
const toggleBtn = document.getElementById('toggleAccordionBtn');
const closeBtn = document.getElementById('closeAccordionBtn');
const accordion = document.getElementById('accordionContent');

if (toggleBtn && accordion && window.bootstrap?.Collapse) {
    const collapse = new bootstrap.Collapse(accordion, { toggle: false });

    toggleBtn.addEventListener('click', () => {
        accordion.classList.contains('show')
            ? collapse.hide()
            : collapse.show();
    });

    closeBtn?.addEventListener('click', () => collapse.hide());
}

/* =========================================================
   🔹 ЗВЁЗДЫ В ФОРМЕ
========================================================= */
const stars = document.querySelectorAll('#addRatingStars .rating-star');
const ratingInput = document.getElementById('addRatingValue');

if (stars.length && ratingInput) {
    stars.forEach(star => {
        star.addEventListener('click', () => {
            const val = +star.dataset.value;
            ratingInput.value = val;

            stars.forEach(s => {
                s.src = +s.dataset.value <= val
                    ? '/storage/icon/button/award-stars_active.svg'
                    : '/storage/icon/button/award-stars_disable.svg';
            });
        });
    });
}

});
