// console.log('tabs.js loaded');

document.addEventListener('DOMContentLoaded', () => {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    // 🛡 Защита: если вкладок нет — выходим
    if (!tabButtons.length || !tabContents.length) return;

    function openTab(tab) {
        // Скрываем всё
        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabContents.forEach(content => {
            content.style.display = 'none';
        });

        // Активируем нужную
        const activeBtn = document.querySelector(`[data-tab="${tab}"]`);
        const activeContent = document.getElementById(tab);

        if (!activeBtn || !activeContent) return;

        activeBtn.classList.add('active');
        activeContent.style.display = 'block';

        // Обновляем hash без перезагрузки
        history.replaceState(null, '', `#${tab}`);
    }

    // Клики по кнопкам
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            if (tab) openTab(tab);
        });
    });

    // Начальная вкладка
    const initialTab =
        location.hash.replace('#', '') ||
        tabButtons[0]?.dataset.tab;

    if (initialTab) {
        openTab(initialTab);
    }
});
