document.addEventListener('DOMContentLoaded', function() {
    // Страница может содержать несколько независимых виджетов поиска
    // одновременно (десктопный в компактном хедере + мобильный,
    // разворачивающийся по клику на лупу) — инициализируем каждый
    // отдельно, без завязки на глобальные id.
    document.querySelectorAll('.live-search-widget').forEach(initSearchWidget);

    function initSearchWidget(widget) {
        const searchInput = widget.querySelector('input.header-search');
        const resultsContainer = widget.querySelector('.search-results-dropdown');
        const searchBtn = widget.querySelector('.search_btn');
        const noseIcon = widget.querySelector('.search_btn img');

        if (!searchInput || !resultsContainer) return;

        // ── Анимация «принюхивания» иконки-носа в кнопке поиска ──
        // Пока пользователь печатает — иконка «нюхает» (двигается/крутится),
        // как только набор текста прекращается — анимация останавливается.
        let sniffTimeout = null;

        function startSniffing() {
            if (!noseIcon) return;
            noseIcon.classList.add('is-sniffing');
            clearTimeout(sniffTimeout);
            sniffTimeout = setTimeout(stopSniffing, 500);
        }

        function stopSniffing() {
            if (!noseIcon) return;
            noseIcon.classList.remove('is-sniffing');
        }

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
            searchBtn.addEventListener('click', performFullSearch);
        }

        function renderResultItem(item) {
            switch (item.type) {
                case 'clinic':
                    return `
                        <a href="/clinics/${item.city_slug}/${item.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
                            <img src="${item.image}" class="search-img-thumb" alt="logo" style="width:40px; height:40px; object-fit:cover; border-radius:4px;">
                            <div class="ms-2">
                                <div class="result-title text-primary"><small>🏥 Клиника:</small> ${item.name}</div>
                                <div class="result-sub-small text-muted" style="font-size: 0.85rem;">${item.address}</div>
                            </div>
                        </a>`;

                case 'organization': {
                    const category = item.category_name ? `<span class="text-muted small">(${item.category_name})</span>` : '';
                    return `
                        <a href="/organizations/${item.city_slug}/${item.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
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

            startSniffing();

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
    }
});