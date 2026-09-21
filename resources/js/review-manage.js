/**
 * Редактирование и удаление собственных отзывов.
 * Работает и в личном кабинете, и в карточках организации, клиники, врача и специалиста.
 *
 * Разметка:
 *   <button data-review-edit data-review='{"id":1,"rating":5,...}'>   — открыть форму редактирования
 *   <button data-review-delete="1">                                    — удалить (с подтверждением)
 *
 * После успеха генерируется событие document → 'review:changed'. Если его никто не отменил
 * (preventDefault), страница перезагружается, а уведомление показывается после перезагрузки.
 * Личный кабинет отменяет событие и просто перерисовывает список отзывов.
 */
import { notify, notifyAfterReload } from './notify';

const STAR_ON = '/storage/icon/button/award-stars_active.svg';
const STAR_OFF = '/storage/icon/button/award-stars_disable.svg';

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

function esc(value) {
    const div = document.createElement('div');
    div.textContent = value == null ? '' : String(value);
    return div.innerHTML;
}

function finish(message) {
    const event = new CustomEvent('review:changed', { cancelable: true });
    if (document.dispatchEvent(event)) {
        notifyAfterReload(message, 'success');
        window.location.reload();
    } else {
        notify(message, 'success');
    }
}

async function request(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        headers: {
            'X-CSRF-TOKEN': csrf(),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers || {}),
        },
    });
    let data = null;
    try { data = await response.json(); } catch (e) { /* пустой ответ */ }
    return { ok: response.ok, status: response.status, data };
}

function errorText(result) {
    if (result.data?.errors) {
        return Object.values(result.data.errors).flat().map(m => `<div>${esc(m)}</div>`).join('');
    }
    if (result.status === 403) return 'Нет прав для изменения этого отзыва.';
    if (result.status === 419) return 'Сессия устарела. Обновите страницу и попробуйте ещё раз.';
    return esc(result.data?.message || 'Не удалось выполнить действие. Попробуйте ещё раз.');
}

/* ------------------------------------------------------------------ */
/*  Модалка редактирования                                             */
/* ------------------------------------------------------------------ */
let editModalEl = null;
let editModal = null;
let current = null;          // редактируемый отзыв
let rating = 0;
let removedPhotos = new Set();
let removedReceipts = new Set();

function ensureEditModal() {
    if (editModalEl) return;

    editModalEl = document.createElement('div');
    editModalEl.className = 'modal fade';
    editModalEl.id = 'editReviewModal';
    editModalEl.tabIndex = -1;
    editModalEl.setAttribute('aria-hidden', 'true');
    editModalEl.innerHTML = `
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:16px;">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold">Редактировать отзыв</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger small py-2 d-none" data-role="errors"></div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Оценка</label>
                        <div class="d-flex gap-1" data-role="stars"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Понравилось</label>
                        <input type="text" class="form-control" maxlength="500" data-role="liked" placeholder="Что вам понравилось">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Не понравилось</label>
                        <input type="text" class="form-control" maxlength="500" data-role="disliked" placeholder="Что можно улучшить">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ваш отзыв</label>
                        <textarea class="form-control" rows="4" maxlength="2000" data-role="content" placeholder="Напишите свой отзыв..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Фотографии</label>
                        <div class="d-flex flex-wrap gap-2 mb-2" data-role="photos"></div>
                        <input type="file" class="form-control" multiple accept="image/*" data-role="new-photos">
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Чеки</label>
                        <div class="mb-2" data-role="receipts"></div>
                        <input type="file" class="form-control" multiple accept="image/*,application/pdf" data-role="new-receipts">
                        <div class="form-text">Новый чек отправляется на проверку.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary px-4" data-role="save">Сохранить</button>
                </div>
            </div>
        </div>`;
    document.body.appendChild(editModalEl);
    editModal = new window.bootstrap.Modal(editModalEl);

    const stars = editModalEl.querySelector('[data-role="stars"]');
    for (let i = 1; i <= 5; i++) {
        const img = document.createElement('img');
        img.src = STAR_OFF;
        img.width = 30;
        img.alt = 'звезда';
        img.dataset.value = i;
        img.style.cursor = 'pointer';
        img.addEventListener('click', () => setRating(i));
        stars.appendChild(img);
    }

    editModalEl.querySelector('[data-role="save"]').addEventListener('click', saveEdit);
}

function setRating(value) {
    rating = value;
    editModalEl.querySelectorAll('[data-role="stars"] img').forEach(img => {
        img.src = +img.dataset.value <= value ? STAR_ON : STAR_OFF;
    });
}

function renderAttachments() {
    const photos = editModalEl.querySelector('[data-role="photos"]');
    photos.innerHTML = '';
    (current.photos || []).filter(p => !removedPhotos.has(p.id)).forEach(p => {
        const box = document.createElement('div');
        box.style.cssText = 'position:relative;width:84px;height:84px;';
        box.innerHTML = `
            <img src="${esc(p.url)}" alt="Фото" style="width:84px;height:84px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6;">
            <button type="button" aria-label="Убрать фото"
                    style="position:absolute;top:-6px;right:-6px;width:22px;height:22px;border:0;border-radius:50%;background:#d64545;color:#fff;line-height:1;font-size:14px;cursor:pointer;">&times;</button>`;
        box.querySelector('button').addEventListener('click', () => {
            removedPhotos.add(p.id);
            renderAttachments();
        });
        photos.appendChild(box);
    });

    const receipts = editModalEl.querySelector('[data-role="receipts"]');
    receipts.innerHTML = '';
    (current.receipts || []).filter(r => !removedReceipts.has(r.id)).forEach(r => {
        const row = document.createElement('div');
        row.className = 'd-flex align-items-center gap-2 small mb-1';
        row.innerHTML = `<span>🧾 ${esc(r.name)}</span>
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2">Убрать</button>`;
        row.querySelector('button').addEventListener('click', () => {
            removedReceipts.add(r.id);
            renderAttachments();
        });
        receipts.appendChild(row);
    });
}

function openEdit(data) {
    ensureEditModal();
    current = data;
    removedPhotos = new Set();
    removedReceipts = new Set();

    editModalEl.querySelector('[data-role="errors"]').classList.add('d-none');
    editModalEl.querySelector('[data-role="liked"]').value = data.liked || '';
    editModalEl.querySelector('[data-role="disliked"]').value = data.disliked || '';
    editModalEl.querySelector('[data-role="content"]').value = data.content || '';
    editModalEl.querySelector('[data-role="new-photos"]').value = '';
    editModalEl.querySelector('[data-role="new-receipts"]').value = '';
    setRating(Number(data.rating) || 0);
    renderAttachments();

    editModal.show();
}

async function saveEdit() {
    const errorsBox = editModalEl.querySelector('[data-role="errors"]');
    const saveBtn = editModalEl.querySelector('[data-role="save"]');
    const showError = html => { errorsBox.innerHTML = html; errorsBox.classList.remove('d-none'); };

    errorsBox.classList.add('d-none');
    if (rating < 1) {
        showError('Выберите оценку от 1 до 5 звёзд.');
        return;
    }
    if (saveBtn.disabled) return;
    saveBtn.disabled = true;

    try {
        // 1. Удаляем отмеченные вложения
        for (const id of removedPhotos) {
            await request(`/review_photos/${id}`, { method: 'DELETE' });
        }
        for (const id of removedReceipts) {
            await request(`/review_receipts/${id}`, { method: 'DELETE' });
        }

        // 2. Сохраняем сам отзыв
        const body = new FormData();
        body.append('rating', rating);
        body.append('liked', editModalEl.querySelector('[data-role="liked"]').value);
        body.append('disliked', editModalEl.querySelector('[data-role="disliked"]').value);
        body.append('content', editModalEl.querySelector('[data-role="content"]').value);
        Array.from(editModalEl.querySelector('[data-role="new-photos"]').files).forEach(f => body.append('photos[]', f));
        Array.from(editModalEl.querySelector('[data-role="new-receipts"]').files).forEach(f => body.append('receipts[]', f));

        const result = await request(`/reviews/${current.id}`, { method: 'POST', body });
        if (!result.ok || !result.data?.success) {
            showError(errorText(result));
            return;
        }

        editModal.hide();
        finish('Отзыв обновлён');
    } catch (e) {
        console.error(e);
        showError('Не удалось сохранить отзыв. Проверьте соединение и попробуйте ещё раз.');
    } finally {
        saveBtn.disabled = false;
    }
}

/* ------------------------------------------------------------------ */
/*  Подтверждение удаления                                             */
/* ------------------------------------------------------------------ */
let deleteModalEl = null;
let deleteModal = null;
let deleteId = null;

function ensureDeleteModal() {
    if (deleteModalEl) return;

    deleteModalEl = document.createElement('div');
    deleteModalEl.className = 'modal fade';
    deleteModalEl.id = 'deleteReviewModal';
    deleteModalEl.tabIndex = -1;
    deleteModalEl.setAttribute('aria-hidden', 'true');
    deleteModalEl.innerHTML = `
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="border-radius:16px;">
                <div class="modal-body text-center p-4">
                    <div style="font-size:38px;line-height:1;">🗑️</div>
                    <h5 class="fw-semibold mt-2">Удалить отзыв?</h5>
                    <p class="text-muted small mb-3">Отзыв и прикреплённые к нему фото и чеки будут удалены без возможности восстановления.</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Отмена</button>
                        <button type="button" class="btn btn-danger px-4" data-role="confirm">Удалить</button>
                    </div>
                </div>
            </div>
        </div>`;
    document.body.appendChild(deleteModalEl);
    deleteModal = new window.bootstrap.Modal(deleteModalEl);

    deleteModalEl.querySelector('[data-role="confirm"]').addEventListener('click', async () => {
        const btn = deleteModalEl.querySelector('[data-role="confirm"]');
        if (btn.disabled || !deleteId) return;
        btn.disabled = true;
        try {
            const result = await request(`/account/reviews/${deleteId}`, { method: 'DELETE' });
            if (result.ok && result.data?.success) {
                deleteModal.hide();
                finish('Отзыв удалён');
            } else {
                deleteModal.hide();
                notify(result.status === 403 ? 'Нет прав для удаления этого отзыва.' : 'Не удалось удалить отзыв.', 'error');
            }
        } catch (e) {
            console.error(e);
            deleteModal.hide();
            notify('Не удалось удалить отзыв. Попробуйте ещё раз.', 'error');
        } finally {
            btn.disabled = false;
        }
    });
}

function openDelete(id) {
    ensureDeleteModal();
    deleteId = id;
    deleteModal.show();
}

/* ------------------------------------------------------------------ */
/*  Делегирование кликов (работает и для динамически созданных карточек) */
/* ------------------------------------------------------------------ */
document.addEventListener('click', event => {
    const editBtn = event.target.closest('[data-review-edit]');
    if (editBtn) {
        event.preventDefault();
        try {
            openEdit(JSON.parse(editBtn.dataset.review));
        } catch (e) {
            console.error('Не удалось прочитать данные отзыва', e);
        }
        return;
    }

    const deleteBtn = event.target.closest('[data-review-delete]');
    if (deleteBtn) {
        event.preventDefault();
        openDelete(deleteBtn.dataset.reviewDelete);
    }
});
