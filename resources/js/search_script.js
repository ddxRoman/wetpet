document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('clinic-live-search');
    const resultsContainer = document.getElementById('search-results');

    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        const query = this.value;

        if (query.length < 2) {
            resultsContainer.innerHTML = '';
            resultsContainer.classList.add('d-none');
            return;
        }

        fetch(`/api/clinics-search?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                // Очищаем контейнер перед отрисовкой
                resultsContainer.innerHTML = '';
                let hasResults = false; // Объявляем ОДИН раз

                // 1. Отрисовка КЛИНИК
                if (data.clinics && data.clinics.length > 0) {
                    data.clinics.forEach(clinic => {
                        hasResults = true;
                        resultsContainer.innerHTML += `
                            <a href="/clinics/${clinic.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
                                <img src="${clinic.image}" class="search-img-thumb" alt="logo" style="width:40px; height:40px; object-fit:cover; border-radius:4px;">
                                <div class="ms-2">
                                    <div class="result-title text-primary"><small>🏥 Клиника:</small> ${clinic.name}</div>
                                    <div class="result-sub-small text-muted" style="font-size: 0.85rem;">${clinic.address}</div>
                                </div>
                            </a>`;
                    });
                }

                // 2. Отрисовка ОРГАНИЗАЦИЙ
                if (data.organizations && data.organizations.length > 0) {
                    data.organizations.forEach(org => {
                        hasResults = true;
                        const category = org.category_name ? `<span class="text-muted small">(${org.category_name})</span>` : '';
                        resultsContainer.innerHTML += `
                            <a href="/organizations/${org.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
                                <img src="${org.image}" class="search-img-thumb" alt="logo" style="width:40px; height:40px; object-fit:cover; border-radius:4px;">
                                <div class="ms-2">
                                    <div class="result-title text-success">
                                        <small>🏢</small> ${org.name} ${category}
                                    </div>
                                    <div class="result-sub-small text-muted" style="font-size: 0.85rem;">${org.address}</div>
                                </div>
                            </a>`;
                    });
                }

                // 3. Отрисовка ВРАЧЕЙ
                if (data.doctors && data.doctors.length > 0) {
                    data.doctors.forEach(doctor => {
                        hasResults = true;
                        resultsContainer.innerHTML += `
                            <a href="/doctors/${doctor.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
                                <img src="${doctor.image}" class="search-img-thumb rounded-circle" alt="photo" style="width:40px; height:40px; object-fit:cover;">
                                <div class="ms-2">
                                    <div>
                                        <span class="result-title">🩺 ${doctor.name}</span>
                                        <span class="result-sub-muted small">(${doctor.specialization})</span>
                                    </div>
                                    <div class="result-sub-small text-muted">${doctor.clinic_info}</div>
                                </div>
                            </a>`;
                    });
                }

                // 4. Отрисовка СПЕЦИАЛИСТОВ
                if (data.specialists && data.specialists.length > 0) {
                    data.specialists.forEach(spec => {
                        hasResults = true;
                        resultsContainer.innerHTML += `
                            <a href="/specialists/${spec.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
                                <img src="${spec.image}" class="search-img-thumb rounded-circle" alt="photo" style="width:40px; height:40px; object-fit:cover;">
                                <div class="ms-2">
                                    <div>
                                        <span class="result-title">👤 ${spec.name}</span>
                                        <span class="result-sub-muted small">(${spec.specialization})</span>
                                    </div>
                                    <div class="result-sub-small text-muted">${spec.location_info}</div>
                                </div>
                            </a>`;
                    });
                }

                // 5. Отрисовка ЖИВОТНЫХ (Породы)
                if (data.animals && data.animals.length > 0) {
                    data.animals.forEach(animal => {
                        hasResults = true;
                        resultsContainer.innerHTML += `
                            <a href="/animals/${animal.species_slug}/${animal.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
                                <img src="${animal.image}" class="search-img-thumb" alt="animal" style="width:40px; height:40px; object-fit:cover; border-radius:4px;">
                                <div class="ms-2">
                                    <div class="result-title ">
                                        <small>🐾</small> ${animal.name} 
                                    </div>
                                    <div class="result-sub-small text-muted">${animal.type} ${animal.category}</div>
                                </div>
                            </a>`;
                    });
                }

                // Логика кнопки поиска и Enter
                const searchBtn = document.querySelector('.search_btn');
                function performFullSearch() {
                    const query = searchInput.value.trim();
                    if (query.length >= 2) {
                        const results = resultsContainer.querySelectorAll('.search-result-item');
                        if (results.length === 1) {
                            results[0].click();
                        } else {
                            window.location.href = `/search?q=${encodeURIComponent(query)}`;
                        }
                    }
                }

                if (searchBtn) {
                    // Удаляем старый слушатель перед добавлением (на всякий случай)
                    searchBtn.onclick = performFullSearch;
                }

                if (hasResults) {
                    resultsContainer.classList.remove('d-none');
                } else {
                    resultsContainer.innerHTML = '<div class="p-3 text-center text-muted">Ничего не найдено</div>';
                    resultsContainer.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Search error:', error);
            });
    });

    // Обработка Enter
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const query = this.value.trim();
            if (query.length >= 2) {
                const results = resultsContainer.querySelectorAll('.search-result-item');
                if (results.length === 1) {
                    results[0].click();
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