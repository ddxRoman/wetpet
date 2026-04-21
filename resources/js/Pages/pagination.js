document.addEventListener('DOMContentLoaded', () => {
    const loadMoreBtn = document.getElementById('load-more');
    // Ищем контейнер для докторов ИЛИ для клиник
    const listContainer = document.querySelector('.doctors-list .row') || document.getElementById('clinics-grid');
    const container = document.getElementById('load-more-container');

    if (loadMoreBtn && listContainer) {
        loadMoreBtn.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            this.disabled = true;
            this.innerText = 'Загрузка...';

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(data => {
                const parser = new DOMParser();
                const htmlDoc = parser.parseFromString(data, 'text/html');
                
                // Ищем новые элементы. 
                // Для клиник мы добавили класс .clinic-item, для докторов .col-lg-3
                const newItems = htmlDoc.querySelectorAll('.clinic-item, .doctors-list .col-lg-3');
                
                if (newItems.length > 0) {
                    newItems.forEach(item => {
                        listContainer.appendChild(item);
                        // Оживляем тултипы
                        const tooltips = item.querySelectorAll('[data-bs-toggle="tooltip"]');
                        tooltips.forEach(el => new bootstrap.Tooltip(el));
                    });

                    // Ищем кнопку в ПРИШЕДШЕМ контенте
                    const nextBtn = htmlDoc.getElementById('load-more');
                    
                    if (nextBtn) {
                        // Если кнопка в ответе есть, обновляем URL и включаем её
                        this.setAttribute('data-url', nextBtn.getAttribute('data-url'));
                        this.disabled = false;
                        this.innerText = 'Показать еще';
                        // Перемещаем контейнер с кнопкой в самый конец списка
                        listContainer.appendChild(container);
                    } else {
                        // Если в ответе кнопки нет — значит страницы кончились
                        container.remove();
                    }
                } else {
                    container.remove();
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
                this.innerText = 'Ошибка. Повторить?';
                this.disabled = false;
            });
        });
    }
});