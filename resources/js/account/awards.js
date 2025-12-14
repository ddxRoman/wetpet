console.log('awards.js loaded');
/* ===================== 🏅 СЛАЙДЕР НАГРАД ===================== */
document.addEventListener('DOMContentLoaded', () => {
    const carouselEl = document.getElementById('awardCarousel');

    if (!carouselEl) return;

    // ✅ Bootstrap 5 Carousel
    const carousel = new bootstrap.Carousel(carouselEl, {
        interval: false,
        ride: false
    });

    document.querySelectorAll('.award-thumb').forEach((thumb) => {
        thumb.addEventListener('click', (e) => {
            const index = Number(e.currentTarget.dataset.index);
            carousel.to(index);
        });
    });
});
