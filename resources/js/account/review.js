document.addEventListener('DOMContentLoaded', () => {
    const reviewsList = document.getElementById('reviews-list');
    const tabBtn = document.querySelector('[data-tab="reviews"]');
    let loaded = false;

    async function loadReviews() {
        try {
            const res = await fetch(`/account/reviews`, { credentials: 'same-origin' });
            if (!res.ok) throw new Error(await res.text());
            const data = await res.json();
            reviewsList.innerHTML = data.length
                ? data.map(r => renderCard(r)).join('')
                : '<p class="empty-message">Вы пока не оставили ни одного отзыва.</p>';
        } catch (e) {
            console.error(e);
            reviewsList.innerHTML = '<p class="empty-message" style="color:red;">Ошибка при загрузке отзывов.</p>';
        }
    }

    function renderCard(r) {
        const date = new Date(r.created_at).toLocaleDateString('ru-RU');
        const clinicLink = r.clinic_id
            ? `<a href="/clinics/${r.clinic_id}" class="clinic-name">${escapeHtml(r.clinic_name)}</a>`
            : `<span class="clinic-name">Клиника не найдена</span>`;
        const address = [r.region, r.city, r.street, r.house].filter(Boolean).map(escapeHtml).join(', ');

        const photos = (r.photos && r.photos.length)
            ? `<div class="media-group"><strong>Фото:</strong>` + r.photos.map(p => {
                const path = p.photo_path || '';
                return `<div class="media-item">
                    <img src="${path ? '/storage/'+path : '/storage/placeholder.png'}"
                         alt="Фото" class="previewable"
                         data-full="${path ? '/storage/'+path : ''}">
                    <button type="button" class="btn-del-photo" data-photo-id="${p.id}">×</button>
                </div>`;
            }).join('') + `</div>` : '';

        const receipts = (r.receipts && r.receipts.length)
            ? `<div class="media-group"><strong>Чеки:</strong>` + 
                r.receipts.map(f => `
                    <div class="media-item">
                        <a href="/storage/${f.receipt_path}" target="_blank" class="receipt-link">📄 Чек</a>
                        <button type="button" class="btn-del-receipt" data-receipt-id="${f.id}">×</button>
                    </div>
                `).join('') + `</div>` : '';

        return `
            <article class="review-card" data-id="${r.id}">
                <header class="review-header">
                    <div class="left">
                        <div class="clinic-block">${clinicLink}${address ? `<div class="clinic-address">${address}</div>` : ''}</div>
                        <div class="review-date">${date}</div>
                    </div>
                    <div class="right">
                        <button type="button" class="btn-toggle" aria-expanded="false">Редактировать</button>
                        <button type="button" class="btn-delete">Удалить</button>
                    </div>
                </header>
                <div class="review-body">
                    <div class="display">
                        <div class="line"><strong>Понравилось:</strong> <span class="field-liked">${escapeHtml(r.liked ?? '')}</span></div>
                        <div class="line"><strong>Не понравилось:</strong> <span class="field-disliked">${escapeHtml(r.disliked ?? '')}</span></div>
                        <div class="line"><strong>Отзыв:</strong> <span class="field-content">${escapeHtml(r.content ?? '')}</span></div>
                        ${r.rating ? `<div class="line"><strong>Оценка:</strong> ★ ${r.rating}/5</div>` : ''}
                        ${photos}
                        ${receipts}
                    </div>
                    <form class="edit-panel" style="display:none;" enctype="multipart/form-data">
                        <label>Понравилось <input name="liked" type="text" class="input-liked" value="${escapeAttr(r.liked ?? '')}"></label>
                        <label>Не понравилось <input name="disliked" type="text" class="input-disliked" value="${escapeAttr(r.disliked ?? '')}"></label>
                        <label>Отзыв <textarea name="content" class="input-content" rows="4">${escapeHtml(r.content ?? '')}</textarea></label>
                        <label>Оценка (1-5) <input name="rating" type="number" min="1" max="5" class="input-rating" value="${r.rating ?? ''}"></label>
                        <label>Добавить чеки <input type="file" name="receipts[]" class="input-receipts" accept="image/*,application/pdf" multiple></label>
                        <label>Добавить фото <input type="file" name="photo_path" class="input-photos" accept="image/*" multiple></label>
                        <div class="edit-actions">
                            <button type="button" class="btn-cancel">Отмена</button>
                            <button type="button" class="btn-save">Сохранить</button>
                        </div>
                    </form>
                </div>
            </article>
        `;
    }

    function escapeHtml(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
    function escapeAttr(str){ return escapeHtml(str).replace(/\n/g, '&#10;'); }

    // Делегирование кликов
    reviewsList.addEventListener('click', async (e) => {
        const card = e.target.closest('.review-card');
        if (!card) return;

        // Переключение редактирования
        if (e.target.classList.contains('btn-toggle')) {
            const panel = card.querySelector('.edit-panel');
            const open = panel.style.display !== 'none';
            panel.style.display = open ? 'none' : 'block';
            e.target.setAttribute('aria-expanded', (!open).toString());
            return;
        }

        // Отмена
        if (e.target.classList.contains('btn-cancel')) {
            card.querySelector('.edit-panel').style.display = 'none';
            card.querySelector('.btn-toggle').setAttribute('aria-expanded', 'false');
            return;
        }

        // Сохранение
        if (e.target.classList.contains('btn-save')) {
            const id = card.dataset.id;
            const formData = new FormData();
            formData.append('liked', card.querySelector('.input-liked').value.trim());
            formData.append('disliked', card.querySelector('.input-disliked').value.trim());
            formData.append('content', card.querySelector('.input-content').value.trim());
            formData.append('rating', card.querySelector('.input-rating').value.trim());

            // Файлы
            const photos = card.querySelector('.input-photos');
            if (photos?.files.length) Array.from(photos.files).forEach(f => formData.append('photos[]', f));
            const receipts = card.querySelector('.input-receipts');
            if (receipts?.files.length) Array.from(receipts.files).forEach(f => formData.append('receipts[]', f));

            try {
                const res = await fetch(`/reviews/${id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'X-HTTP-Method-Override': 'PUT' },
                    body: formData,
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error(await res.text());
                showToast('Отзыв обновлён', 'success');
                await loadReviews();
            } catch (err) {
                console.error(err);
                showToast('Не удалось сохранить', 'error');
            }
            return;
        }

// Удаление отзыва
if (e.target.classList.contains('btn-delete')) {
    if (!confirm('Удалить этот отзыв?')) return;
    const id = card.dataset.id;

    try {
        const res = await fetch(`/reviews/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                // просим HTML (не JSON) чтобы Laravel выполнил redirect()->to(...)
                'Accept': 'text/html'
            },
            credentials: 'same-origin',
            redirect: 'follow' // по умолчанию, но явно указываем
        });

        // если сервер ответил не ок — прочитаем текст и кинем ошибку
        if (!res.ok) {
            const txt = await res.text();
            throw new Error(txt || `HTTP ${res.status}`);
        }

        // Если fetch последовал за редиректом, res.redirected=true и res.url содержит итоговый адрес
        if (res.redirected && res.url) {
            // Переходим по адресу, который вернул сервер
            window.location.href = res.url;
            return;
        }

        // Иначе — возможно сервер вернул JSON; пробуем разобрать
        const contentType = res.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
            const payload = await res.json();
            // если JSON содержит success — делаем клиентский редирект к вкладке отзывов
            if (payload && payload.success) {
                showToast('Отзыв удалён', 'success');

                const clinicId = card.querySelector('.clinic-name')
                    ?.getAttribute('href')
                    ?.match(/clinics\/(\d+)/)?.[1];

                if (clinicId) {
                    // короткая задержка чтобы пользователь увидел тост
                    setTimeout(() => {
                        window.location.href = `/clinics/${clinicId}?tab=reviews`;
                    }, 700);
                    return;
                } else {
                    // fallback — перезагрузим страницу
                    setTimeout(() => window.location.reload(), 700);
                    return;
                }
            }
        }

        // Если ничего из выше не сработало — как fallback перезагрузим страницу
        window.location.reload();

    } catch (err) {
        console.error(err);
        showToast('Ошибка удаления', 'error');
    }
}



        // Удаление фото
// Удаление фото
if (e.target.classList.contains('btn-del-photo')) {
    if (!confirm('Удалить это фото?')) return;
    const pid = e.target.dataset.photoId;
    try {
        const res = await fetch(`/review_photos/${pid}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            credentials: 'same-origin'
        });
        if (res.ok) {
            e.target.closest('.media-item').remove();
            showToast('Фото удалено', 'success');
        } else {
            showToast('Ошибка удаления фото', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Ошибка при удалении фото', 'error');
    }
    return;
}


        // Удаление чека
// Удаление чека
if (e.target.classList.contains('btn-del-receipt')) {
    if (!confirm('Вы уверены что хотите удалить чек? Его нельзя будет восстановить и если вы передумаете его придётся загружать заново')) return;
    const rid = e.target.dataset.receiptId;
    try {
        const res = await fetch(`/review_receipts/${rid}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            credentials: 'same-origin'
        });
        if (res.ok) {
            e.target.closest('.media-item').remove();
            showToast('Чек удалён', 'success');
        } else {
            showToast('Ошибка удаления чека', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Ошибка при удалении чека', 'error');
    }
    return;
}


        // Просмотр фото
        if (e.target.classList.contains('previewable')) {
            const src = e.target.dataset.full;
            if (src) openPreview(src);
        }
    });

    function openPreview(src) {
        const overlay = document.createElement('div');
        overlay.className = 'photo-preview-overlay';
        overlay.innerHTML = `<div class="photo-preview"><img src="${src}" alt=""><button class="close-preview">×</button></div>`;
        document.body.appendChild(overlay);
        overlay.querySelector('.close-preview').addEventListener('click', () => overlay.remove());
        overlay.addEventListener('click', e => { if (e.target === overlay) overlay.remove(); });
    }

    function showToast(text, type='info') {
        const c = document.getElementById('toast') || Object.assign(document.body.appendChild(document.createElement('div')), {
            id: 'toast', style: 'position:fixed;top:15px;right:15px;z-index:9999;'
        });
        const n = Object.assign(document.createElement('div'), {
            textContent: text,
            style: `background:${type==='error'?'#ef4444':type==='success'?'#10b981':'#333'};color:#fff;padding:8px 12px;margin-top:8px;border-radius:6px;`
        });
        c.appendChild(n);
        setTimeout(()=>n.remove(),3000);
    }

    tabBtn?.addEventListener('click', () => { if (!loaded) { loadReviews(); loaded = true; } });
    if (location.hash === '#reviews') { loadReviews(); loaded = true; }
});

/* ===================== ✅ ФИЛЬТР "РЕАЛЬНЫЕ КЛИЕНТЫ" ===================== */
document.addEventListener('DOMContentLoaded', () => {
    const checkbox = document.getElementById('verifiedOnly');

    if (checkbox) {
        checkbox.addEventListener('change', () => {
            // Получаем все карточки отзывов
            const reviewCards = document.querySelectorAll('.review-card');
            const showVerifiedOnly = checkbox.checked;

            reviewCards.forEach(card => {
                // Проверяем, есть ли внутри карточки элемент с классом .verified-badge
                const hasBadge = !!card.querySelector('.verified-badge');

                // Если фильтр включен и плашки нет — скрываем
                // Если фильтр выключен — показываем все
                card.style.display = (showVerifiedOnly && !hasBadge) ? 'none' : '';
            });
        });

        // Если чекбокс уже был отмечен при загрузке страницы — применяем фильтр сразу
        if (checkbox.checked) {
            checkbox.dispatchEvent(new Event('change'));
        }
    }
});
/* =================== ✅ КОНЕЦ ФИЛЬТРА "РЕАЛЬНЫЕ КЛИЕНТЫ" =================== */

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



/* ===========================================================
   ✅ 2. ПРОСМОТР ФОТО В МОДАЛКЕ С ПЕРЕЛИСТЫВАНИЕМ
=========================================================== */
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('photoModal');
    const modalImg = document.getElementById('modalPhoto');
    const prevBtn = document.getElementById('prevPhoto');
    const nextBtn = document.getElementById('nextPhoto');

    let currentReviewId = null;
    let currentIndex = 0;
    let currentPhotos = [];

    // Открытие модалки при клике на фото
    document.querySelectorAll('.review-photo').forEach(img => {
        img.addEventListener('click', () => {
            currentReviewId = img.dataset.reviewId;
            currentIndex = parseInt(img.dataset.index, 10);

            // Собираем все фото этого отзыва
            currentPhotos = Array.from(
                document.querySelectorAll(`.review-photos[data-review-id="${currentReviewId}"] .review-photo`)
            );

            // Показываем выбранное фото
            modalImg.src = img.src;

            // Открываем модалку Bootstrap
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        });
    });

    // Показ следующего фото
    nextBtn.addEventListener('click', () => {
        if (!currentPhotos.length) return;
        currentIndex = (currentIndex + 1) % currentPhotos.length;
        modalImg.src = currentPhotos[currentIndex].src;
    });

    // Показ предыдущего фото
    prevBtn.addEventListener('click', () => {
        if (!currentPhotos.length) return;
        currentIndex = (currentIndex - 1 + currentPhotos.length) % currentPhotos.length;
        modalImg.src = currentPhotos[currentIndex].src;
    });

    // Перелистывание клавишами ← и →
    document.addEventListener('keydown', e => {
        if (!bootstrap.Modal.getInstance(modal)) return;
        if (e.key === 'ArrowRight') nextBtn.click();
        if (e.key === 'ArrowLeft') prevBtn.click();
    });
});


    // 🌟 Оценка в форме добавления отзыва
    document.addEventListener('DOMContentLoaded', () => {
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



// {{-- JS: управление (Bootstrap + fallback) --}}

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

