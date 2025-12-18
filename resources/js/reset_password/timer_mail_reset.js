document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('resetBtn');
    const timerText = document.getElementById('timerText');

    if (!btn || !timerText) return;

    // время блокировки (сек)
    const COOLDOWN = 30;

    // если сервер подтвердил отправку письма
    if (window.passwordResetSent === true) {
        let seconds = COOLDOWN;

        btn.disabled = true;
        btn.style.opacity = '0.6';

        timerText.textContent = `Повторная отправка через ${seconds} сек.`;

        const interval = setInterval(() => {
            seconds--;
            timerText.textContent = `Повторная отправка через ${seconds} сек.`;

            if (seconds <= 0) {
                clearInterval(interval);
                btn.disabled = false;
                btn.style.opacity = '1';
                timerText.textContent = '';
            }
        }, 1000);
    }
});
