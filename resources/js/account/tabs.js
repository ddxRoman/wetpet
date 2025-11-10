document.addEventListener('DOMContentLoaded', () => {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    function openTab(tab) {
        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabContents.forEach(content => (content.style.display = 'none'));
        document.querySelector(`[data-tab="${tab}"]`)?.classList.add('active');
        document.getElementById(tab).style.display = 'block';
        location.hash = tab;
    }

    tabButtons.forEach(btn => btn.addEventListener('click', () => openTab(btn.dataset.tab)));
    const currentTab = location.hash.replace('#', '') || 'profile';
    openTab(currentTab);
});
