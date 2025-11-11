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
                        <label>Добавить фото <input type="file" class="input-photos" accept="image/*" multiple></label>
                        <label>Добавить чеки <input type="file" class="input-receipts" accept="image/*,application/pdf" multiple></label>
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
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error(await res.text());
                card.remove();
                showToast('Отзыв удалён', 'success');
            } catch {
                showToast('Ошибка удаления', 'error');
            }
            return;
        }

        // Удаление фото
        if (e.target.classList.contains('btn-del-photo')) {
            const pid = e.target.dataset.photoId;
            try {
                const res = await fetch(`/review_photos/${pid}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    credentials: 'same-origin'
                });
                if (res.ok) e.target.closest('.media-item').remove();
            } catch (err) { console.error(err); }
            return;
        }

        // Удаление чека
        if (e.target.classList.contains('btn-del-receipt')) {
            const rid = e.target.dataset.receiptId;
            try {
                const res = await fetch(`/review_receipts/${rid}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    credentials: 'same-origin'
                });
                if (res.ok) e.target.closest('.media-item').remove();
            } catch (err) { console.error(err); }
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
