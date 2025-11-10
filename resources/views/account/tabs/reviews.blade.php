<div class="tab-content" id="reviews" style="display:none;">
    <h2>Отзывы пользователя {{ $user->nickname ?? $user->name }}</h2>
    <div id="reviews-list" class="reviews-container">
        <p class="empty-message">Загрузка отзывов...</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const reviewsList = document.getElementById('reviews-list');
    const tabBtn = document.querySelector('[data-tab="reviews"]');
    const csrf = '{{ csrf_token() }}';
    let loaded = false;

    async function loadReviews() {
        try {
            const res = await fetch(`/account/reviews/{{ $user->id }}`);
            if (!res.ok) throw new Error('Ошибка загрузки отзывов');
            const data = await res.json();
            if (!data.length) {
                reviewsList.innerHTML = '<p class="empty-message">Вы пока не оставили ни одного отзыва.</p>';
                return;
            }
            reviewsList.innerHTML = data.map(r => renderCard(r)).join('');
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

        const photos = r.photos?.length
            ? `<div class="media-group"><strong>Фото:</strong> ${r.photos.map(p => {
                const path = p.photo_path ?? '';
                return `
                    <div class="media-item">
                        <img src="${path ? '/storage/' + path : ''}" alt="Фото" class="previewable" data-full="${path ? '/storage/' + path : ''}">
                        <button type="button" class="btn-del-photo" data-photo-id="${p.id}">×</button>
                    </div>`;
            }).join('')}</div>` : '';

        const receipts = r.receipts?.length
            ? `<div class="media-group"><strong>Чеки:</strong> ${r.receipts.map(f => {
                const path = f.receipt_path ?? '';
                return `
                    <div class="media-item">
                        <a href="${path ? '/storage/' + path : '#'}" target="_blank" class="receipt-link">📄 Чек</a>
                        <button type="button" class="btn-del-receipt" data-receipt-id="${f.id}">×</button>
                    </div>`;
            }).join('')}</div>` : '';

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
                        ${r.rating ? `<div class="line"><strong>Оценка:</strong> <span class="field-rating">★ ${r.rating}/5</span></div>` : ''}
                        ${photos}
                        ${receipts}
                    </div>

                    <form class="edit-panel" style="display:none;">
                        <label>Понравилось
                            <input name="liked" type="text" class="input-liked" value="${escapeAttr(r.liked ?? '')}">
                        </label>
                        <label>Не понравилось
                            <input name="disliked" type="text" class="input-disliked" value="${escapeAttr(r.disliked ?? '')}">
                        </label>
                        <label>Отзыв
                            <textarea name="content" class="input-content" rows="4">${escapeHtml(r.content ?? '')}</textarea>
                        </label>
                        <label>Оценка (1-5)
                            <input name="rating" type="number" class="input-rating" min="1" max="5" value="${r.rating ?? ''}">
                        </label>
                        <label>Добавить фото
                            <input type="file" class="input-photos" accept="image/*" multiple>
                        </label>
                        <label>Добавить чеки
                            <input type="file" class="input-receipts" accept="image/*,application/pdf" multiple>
                        </label>
                        <div class="edit-actions">
                            <button type="button" class="btn-cancel">Отмена</button>
                            <button type="button" class="btn-save">Сохранить</button>
                        </div>
                    </form>
                </div>
            </article>`;
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

    reviewsList.addEventListener('click', async (e) => {
        const card = e.target.closest('.review-card');
        if (!card) return;

        if (e.target.classList.contains('btn-toggle')) {
            const panel = card.querySelector('.edit-panel');
            const isOpen = panel.style.display !== 'none';
            panel.style.display = isOpen ? 'none' : 'block';
            e.target.setAttribute('aria-expanded', (!isOpen).toString());
            return;
        }

        if (e.target.classList.contains('btn-cancel')) {
            card.querySelector('.edit-panel').style.display = 'none';
            card.querySelector('.btn-toggle').setAttribute('aria-expanded', 'false');
            return;
        }

        // ✅ Сохранение
        if (e.target.classList.contains('btn-save')) {
            const id = card.dataset.id;
            const formData = new FormData();
            formData.append('_token', csrf);
            formData.append('_method', 'PUT');
            formData.append('liked', card.querySelector('.input-liked').value.trim());
            formData.append('disliked', card.querySelector('.input-disliked').value.trim());
            formData.append('content', card.querySelector('.input-content').value.trim());
            formData.append('rating', card.querySelector('.input-rating').value.trim());

            Array.from(card.querySelector('.input-photos').files).forEach(f => formData.append('photos[]', f));
            Array.from(card.querySelector('.input-receipts').files).forEach(f => formData.append('receipts[]', f));

            try {
                const res = await fetch(`/reviews/${id}`, { method: 'POST', body: formData });
                if (!res.ok) throw new Error('Ошибка сохранения');
                showToast('Отзыв обновлён', 'success');
                loadReviews();
            } catch (err) {
                console.error(err);
                showToast('Не удалось сохранить (403/419)', 'error');
            }
            return;
        }

        // ✅ Удаление
        if (e.target.classList.contains('btn-delete')) {
            if (!confirm('Удалить этот отзыв?')) return;
            const id = card.dataset.id;
            const formData = new FormData();
            formData.append('_token', csrf);
            formData.append('_method', 'DELETE');
            try {
                const res = await fetch(`/reviews/${id}`, {
                    method: 'POST',
                    body: formData
                });
                if (!res.ok) throw new Error('Ошибка удаления');
                card.remove();
                showToast('Отзыв удалён', 'success');
            } catch (err) {
                console.error(err);
                showToast('Ошибка удаления (403/419)', 'error');
            }
            return;
        }

        // ✅ Удаление фото
        if (e.target.classList.contains('btn-del-photo')) {
            const pid = e.target.dataset.photoId;
            const form = new FormData();
            form.append('_token', csrf);
            form.append('_method', 'DELETE');
            try {
                const res = await fetch(`/review_photos/${pid}`, {
                    method: 'POST',
                    body: form
                });
                if (res.ok) e.target.closest('.media-item').remove();
            } catch {}
        }

        // ✅ Удаление чека
        if (e.target.classList.contains('btn-del-receipt')) {
            const rid = e.target.dataset.receiptId;
            const form = new FormData();
            form.append('_token', csrf);
            form.append('_method', 'DELETE');
            try {
                const res = await fetch(`/review_receipts/${rid}`, {
                    method: 'POST',
                    body: form
                });
                if (res.ok) e.target.closest('.media-item').remove();
            } catch {}
        }

        // ✅ Превью фото
        if (e.target.classList.contains('previewable')) {
            const fullSrc = e.target.dataset.full;
            openPreview(fullSrc);
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
</script>
<style>
.reviews-container { display:flex; flex-direction:column; gap:14px; }
.review-card { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:12px; box-shadow:0 2px 6px rgba(0,0,0,0.04); }
.review-header { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; }
.review-header .right { display:flex; gap:6px; }
.review-date { color:#9ca3af; font-size:13px; }
.clinic-name { color:#2563eb; font-weight:700; text-decoration:none; }
.clinic-address { color:#6b7280; font-size:13px; }
.display .line { margin:5px 0; }
.field-rating { color:#f59e0b; }

.media-group { margin-top:8px; display:flex; flex-wrap:wrap; gap:6px; }
.media-item { position:relative; display:inline-block; }
.media-item img { width:80px; height:80px; object-fit:cover; border-radius:6px; border:1px solid #ddd; cursor:pointer; transition:transform .2s; }
.media-item img:hover { transform:scale(1.05); }
.media-item .btn-del-photo,
.media-item .btn-del-receipt {
    position:absolute; top:-6px; right:-6px;
    background:#ef4444; color:#fff; border:none; border-radius:50%;
    width:20px; height:20px; font-size:14px; cursor:pointer;
}
.receipt-link { display:inline-block; background:#f3f4f6; padding:4px 8px; border-radius:6px; text-decoration:none; color:#111; font-size:14px; }

.photo-preview-overlay {
    position:fixed; inset:0; background:rgba(0,0,0,.7);
    display:flex; justify-content:center; align-items:center;
    z-index:10000;
}
.photo-preview {
    position:relative;
}
.photo-preview img {
    max-width:90vw;
    max-height:90vh;
    border-radius:10px;
}
.photo-preview .close-preview {
    position:absolute;
    top:-10px; right:-10px;
    background:#ef4444; color:#fff;
    border:none; border-radius:50%;
    width:30px; height:30px;
    font-size:20px;
    cursor:pointer;
}

.edit-panel label { display:block; margin-bottom:8px; font-size:13px; }
.edit-panel input, .edit-panel textarea { width:100%; border:1px solid #d1d5db; border-radius:6px; padding:6px 8px; font-size:14px; }
.edit-actions { display:flex; justify-content:flex-end; gap:8px; }
.btn-toggle, .btn-delete, .btn-save, .btn-cancel { border:none; border-radius:6px; padding:6px 10px; cursor:pointer; font-weight:600; }
.btn-toggle { background:#3b82f6; color:#fff; }
.btn-delete { background:#ef4444; color:#fff; }
.btn-save { background:#10b981; color:#fff; }
.btn-cancel { background:#e5e7eb; color:#111; }
</style>
