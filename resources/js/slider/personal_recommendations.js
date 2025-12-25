document.addEventListener('DOMContentLoaded', function () {
    const radios = Array.from(
        document.querySelectorAll('.carousel input[type="radio"]')
    );

    if (!radios.length) return;

    const delay = 5000;
    let timer;

    const startAutoSlide = () => {
        clearInterval(timer);
        timer = setInterval(() => {
            const currentIndex = radios.findIndex(radio => radio.checked);
            const nextIndex = (currentIndex + 1) % radios.length;
            radios[nextIndex].checked = true;
        }, delay);
    };

    radios.forEach(radio => {
        radio.addEventListener('change', startAutoSlide);
    });

    startAutoSlide();
});

