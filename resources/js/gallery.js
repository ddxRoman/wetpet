document.addEventListener('DOMContentLoaded', function () {
    initPublicLightbox();
});

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
