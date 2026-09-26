document.addEventListener('DOMContentLoaded', function () {
    initPublicLightbox();
    initGalleryManagers();
});

function csrfToken() {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.content : '';
}

/* ==========================================================================
   Публичная модалка-галерея (клик по обложке на странице организации/
   клиники/врача/специалиста)
   ========================================================================== */
function initPublicLightbox() {
    const modalEl = document.getElementById('galleryModal');
    if (!modalEl || typeof window.bootstrap === 'undefined') return;

    const imgEl = document.getElementById('galleryModalImg');
    const counterEl = document.getElementById('galleryCounter');
    const prevBtn = document.getElementById('galleryPrevBtn');
    const nextBtn = document.getElementById('galleryNextBtn');
    const modal = new window.bootstrap.Modal(modalEl);

    let photos = [];
    let index = 0;

    function render() {
        if (!photos.length) return;
        imgEl.src = photos[index];
        counterEl.textContent = photos.length > 1 ? `${index + 1} / ${photos.length}` : '';
        const showNav = photos.length > 1;
        prevBtn.style.display = showNav ? '' : 'none';
        nextBtn.style.display = showNav ? '' : 'none';
    }

    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('.js-gallery-cover');
        if (!trigger) return;

        try {
            photos = JSON.parse(trigger.getAttribute('data-photos') || '[]');
        } catch (err) {
            photos = [];
        }
        if (!photos.length) return;

        index = 0;
        render();
        modal.show();
    });

    prevBtn.addEventListener('click', function () {
        if (!photos.length) return;
        index = (index - 1 + photos.length) % photos.length;
        render();
    });

    nextBtn.addEventListener('click', function () {
        if (!photos.length) return;
        index = (index + 1) % photos.length;
        render();
    });

    modalEl.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') prevBtn.click();
        if (e.key === 'ArrowRight') nextBtn.click();
    });
}

/* ==========================================================================
   Менеджер галереи в личном кабинете владельца: загрузка, удаление,
   перетаскивание для смены порядка.
   ========================================================================== */
function initGalleryManagers() {
    document.querySelectorAll('.gallery-manager').forEach(initOneGalleryManager);
}

function initOneGalleryManager(manager) {
    const grid = manager.querySelector('.gallery-manager-grid');
    const input = manager.querySelector('.gallery-manager-input');
    const msg = manager.querySelector('.gallery-manager-msg');
    const counterEl = manager.querySelector('.gallery-manager-counter');
    const addBtnWrapper = manager.querySelector('.gallery-manager-add');

    const uploadUrl = manager.dataset.uploadUrl;
    const reorderUrl = manager.dataset.reorderUrl;
    let limit = parseInt(manager.dataset.limit || '1', 10);

    function currentCount() {
        return grid.querySelectorAll('.gallery-manager-item').length;
    }

    function updateCounter() {
        if (counterEl) counterEl.textContent = `${currentCount()}/${limit}`;
        if (addBtnWrapper) {
            addBtnWrapper.classList.toggle('d-none', currentCount() >= limit);
        }
    }

    function showMessage(text) {
        if (!msg) return;
        msg.textContent = text || '';
    }

    function makeItem(photo) {
        const item = document.createElement('div');
        item.className = 'gallery-manager-item';
        item.draggable = true;
        item.dataset.photoId = photo.id;
        item.innerHTML = `
            <img src="${photo.url}" alt="">
            <button type="button" class="gallery-manager-remove" aria-label="Удалить фото">&times;</button>
        `;
        return item;
    }

    // ── Загрузка ──
    if (input) {
        input.addEventListener('change', function () {
            const files = Array.from(input.files || []);
            if (!files.length) return;

            showMessage('');

            const formData = new FormData();
            files.forEach(f => formData.append('photos[]', f));

            fetch(uploadUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
                body: formData,
            })
                .then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        throw new Error(data.message || 'Не удалось загрузить фото');
                    }
                    return data;
                })
                .then((data) => {
                    (data.photos || []).forEach(photo => grid.appendChild(makeItem(photo)));
                    if (data.skipped > 0) {
                        showMessage(`Загружено не всё: превышен лимит фотографий.`);
                    }
                    updateCounter();
                })
                .catch((err) => {
                    showMessage(err.message);
                })
                .finally(() => {
                    input.value = '';
                });
        });
    }

    // ── Удаление ──
    grid.addEventListener('click', function (e) {
        const btn = e.target.closest('.gallery-manager-remove');
        if (!btn) return;

        const item = btn.closest('.gallery-manager-item');
        const photoId = item.dataset.photoId;
        if (!confirm('Удалить эту фотографию?')) return;

        fetch(`${manager.dataset.baseUrl}/photo/${photoId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
        })
            .then(res => {
                if (!res.ok) throw new Error('Не удалось удалить фото');
                item.remove();
                updateCounter();
            })
            .catch(err => showMessage(err.message));
    });

    // ── Перетаскивание (нативный HTML5 drag & drop) ──
    let dragged = null;

    grid.addEventListener('dragstart', function (e) {
        const item = e.target.closest('.gallery-manager-item');
        if (!item) return;
        dragged = item;
        item.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move';
    });

    grid.addEventListener('dragend', function () {
        if (dragged) dragged.classList.remove('is-dragging');
        dragged = null;
        sendOrder();
    });

    grid.addEventListener('dragover', function (e) {
        e.preventDefault();
        const item = e.target.closest('.gallery-manager-item');
        if (!item || item === dragged || !dragged) return;

        const rect = item.getBoundingClientRect();
        const after = (e.clientX - rect.left) > rect.width / 2;
        grid.insertBefore(dragged, after ? item.nextSibling : item);
    });

    function sendOrder() {
        if (!reorderUrl) return;
        const order = Array.from(grid.querySelectorAll('.gallery-manager-item')).map(el => el.dataset.photoId);

        fetch(reorderUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ order }),
        }).catch(() => showMessage('Не удалось сохранить новый порядок фото'));
    }

    updateCounter();
}
