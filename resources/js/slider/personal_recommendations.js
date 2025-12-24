document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('.carousel input[type="radio"]');
    if (!radios.length) return;

    let current = 0;
    const delay = 5000; // 5 секунд

    setInterval(() => {
        radios[current].checked = false;
        current = (current + 1) % radios.length;
        radios[current].checked = true;
    }, delay);
});
