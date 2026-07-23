document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('clinic-live-search');
    const resultsContainer = document.getElementById('search-results');

    if (!searchInput) return;
    function renderResultItem(item) {
        switch (item.type) {
            case 'clinic':
                return `
                    <a href="/clinics/${item.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
                        <img src="${item.image}" class="search-img-thumb" alt="logo" style="width:40px; height:40px; object-fit:cover; border-radius:4px;">
                        <div class="ms-2">
                            <div class="result-title text-primary"><small>🏥 Клиника:</small> ${item.name}</div>
                            <div class="result-sub-small text-muted" style="font-size: 0.85rem;">${item.address}</div>
                        </div>
                    </a>`;

            case 'organization': {
                const category = item.category_name ? `<span class="text-muted small">(${item.category_name})</span>` : '';
                return `
                    <a href="/organizations/${item.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
                        <img src="${item.image}" class="search-img-thumb" alt="logo" style="width:40px; height:40px; object-fit:cover; border-radius:4px;">
                        <div class="ms-2">
                            <div class="result-title text-success">
                                <small>🏢</small> ${item.name} ${category}
                            </div>
                            <div class="result-sub-small text-muted" style="font-size: 0.85rem;">${item.address}</div>
                        </div>
                    </a>`;
            }

            case 'doctor':
                return `
                    <a href="/doctors/${item.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
                        <img src="${item.image}" class="search-img-thumb rounded-circle" alt="photo" style="width:40px; height:40px; object-fit:cover;">
                        <div class="ms-2">
                            <div>
                                <span class="result-title">🩺 ${item.name}</span>
                                <span class="result-sub-muted small">(${item.specialization})</span>
                            </div>
                            <div class="result-sub-small text-muted">${item.clinic_info}</div>
                        </div>
                    </a>`;

            case 'specialist':
                return `
                    <a href="/specialists/${item.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
                        <img src="${item.image}" class="search-img-thumb rounded-circle" alt="photo" style="width:40px; height:40px; object-fit:cover;">
                        <div class="ms-2">
                            <div>
                                <span class="result-title">👤 ${item.name}</span>
                                <span class="result-sub-muted small">(${item.specialization})</span>
                            </div>
                            <div class="result-sub-small text-muted">${item.location_info}</div>
                        </div>
                    </a>`;

            case 'animal':
                return `
                    <a href="/animals/${item.species_slug}/${item.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
                        <img src="${item.image}" class="search-img-thumb" alt="animal" style="width:40px; height:40px; object-fit:cover; border-radius:4px;">
                        <div class="ms-2">
                            <div class="result-title ">
                                <small>🐾</small> ${item.name}
                            </div>
                            <div class="result-sub-small text-muted">${item.category ?? ''}</div>
                        </div>
                    </a>`;

            default:
                return '';
        }
    }

    // Защита от гонки запросов: отменяем предыдущий незавершённый запрос
    // и игнорируем ответы, пришедшие не на последний отправленный запрос.
    // Без этого при быстром наборе текста более ранний (по времени ухода)
    // запрос может завершиться позже более позднего и затереть корректный
    // результат сообщением "Ничего не найдено" — именно это происходило
    // на проде из-за более высокой и нестабильной задержки сети/БД.
    let currentController = null;
    let requestSeq = 0;
    let debounceTimer = null;

    function performSearch(query) {
        if (currentController) {
            currentController.abort();
        }
        currentController = new AbortController();
        const thisSeq = ++requestSeq;

        fetch(`/api/clinics-search?q=${encodeURIComponent(query)}`, {
            signal: currentController.signal
        })
            .then(response => response.json())
            .then(data => {
                // Ответ устарел — пришёл не на последний запрос, игнорируем
                if (thisSeq !== requestSeq) return;

                resultsContainer.innerHTML = '';

                const results = data.results || [];

                if (results.length > 0) {
                    resultsContainer.innerHTML = results.map(renderResultItem).join('');
                }

                // Логика кнопки поиска и Enter
                const searchBtn = document.querySelector('.search_btn');
                function performFullSearch() {
                    const query = searchInput.value.trim();
                    if (query.length >= 2) {
                        const items = resultsContainer.querySelectorAll('.search-result-item');
                        if (items.length === 1) {
                            items[0].click();
                        } else {
                            window.location.href = `/search?q=${encodeURIComponent(query)}`;
                        }
                    }
                }

                if (searchBtn) {
                    // Удаляем старый слушатель перед добавлением (на всякий случай)
                    searchBtn.onclick = performFullSearch;
                }

                if (results.length > 0) {
                    resultsContainer.classList.remove('d-none');
                } else {
                    resultsContainer.innerHTML = '<div class="p-3 text-center text-muted">Ничего не найдено</div>';
                    resultsContainer.classList.remove('d-none');
                }
            })
            .catch(error => {
                if (error.name !== 'AbortError') {
                    console.error('Search error:', error);
                }
            });
    }

    searchInput.addEventListener('input', function() {
        const query = this.value;

        if (query.length < 2) {
            if (currentController) currentController.abort();
            clearTimeout(debounceTimer);
            resultsContainer.innerHTML = '';
            resultsContainer.classList.add('d-none');
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => performSearch(query), 200);
    });

    // Обработка Enter
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = this.value.trim();
            if (query.length >= 2) {
                const items = resultsContainer.querySelectorAll('.search-result-item');
                if (items.length === 1) {
                    items[0].click();
                } else {
                    window.location.href = `/search?q=${encodeURIComponent(query)}`;
                }
            }
        }
    });

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.classList.add('d-none');
        }
    });

    searchInput.addEventListener('focus', function() {
        if (this.value.length >= 2 && resultsContainer.innerHTML !== '') {
            resultsContainer.classList.remove('d-none');
        }
    });
});