    /* ===================== 🏅 СЛАЙДЕР НАГРАД ===================== */
    const carousel = document.getElementById('awardCarousel');
    if (carousel) {
        const bsCarousel = new bootstrap.Carousel(carousel);
        document.querySelectorAll('.award-thumb').forEach((thumb) => {
            thumb.addEventListener('click', (event) => {
                const index = parseInt(event.currentTarget.dataset.index);
                bsCarousel.to(index);
            });
        });
    }
