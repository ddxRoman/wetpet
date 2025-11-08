document.addEventListener('DOMContentLoaded', function () {
    const stars = document.querySelectorAll('.rating-star');
    const ratingValue = document.getElementById('ratingValue');
    const ratingError = document.getElementById('ratingError');
    const form = document.getElementById('reviewForm');

    if (!form) return;

    // Наведение и выбор звёзд
    stars.forEach(star => {
        star.addEventListener('mouseover', function () {
            const value = this.dataset.value;
            stars.forEach(s => {
                s.src = s.dataset.value <= value
                    ? "{{ asset('storage/icon/button/award-stars_active.svg') }}"
                    : "{{ asset('storage/icon/button/award-stars_disable.svg') }}";
            });
        });

        star.addEventListener('mouseout', function () {
            const value = ratingValue.value;
            stars.forEach(s => {
                s.src = s.dataset.value <= value
                    ? "{{ asset('storage/icon/button/award-stars_active.svg') }}"
                    : "{{ asset('storage/icon/button/award-stars_disable.svg') }}";
            });
        });

        star.addEventListener('click', function () {
            ratingValue.value = this.dataset.value;
            ratingError.classList.add('d-none');
        });
    });

    // Проверка при отправке формы
    form.addEventListener('submit', function (e) {
        const rating = parseInt(ratingValue.value);
        if (isNaN(rating) || rating < 1 || rating > 5) {
            e.preventDefault();
            ratingError.classList.remove('d-none');
            ratingError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});
