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

        // Запрос к твоему API
        fetch(`/api/clinics-search?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                resultsContainer.innerHTML = '';
                let hasResults = false;

                // 1. Отрисовка КЛИНИК
                if (data.clinics && data.clinics.length > 0) {
                    data.clinics.forEach(clinic => {
                        hasResults = true;
                        resultsContainer.innerHTML += `
                            <a href="/clinics/${clinic.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
                                <img src="${clinic.image}" class="search-img-thumb" alt="logo">
                                <div class="ms-2">
                                    <div class="result-title text-primary"><small>🏥 Клиника:</small> ${clinic.name}</div>
                                    <div class="result-sub-small text-muted">${clinic.address}</div>
                                </div>
                            </a>`;
                    });
                }

                // 2. Отрисовка ОРГАНИЗАЦИЙ (Груминг-центры, приюты и т.д.)
                if (data.organizations && data.organizations.length > 0) {
                    data.organizations.forEach(org => {
                        hasResults = true;
                        resultsContainer.innerHTML += `
                            <a href="/organizations/${org.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
                                <img src="${org.image}" class="search-img-thumb" alt="logo">
                                <div class="ms-2">
                                    <div class="result-title text-success"><small>🏢 Организация:</small> ${org.name}</div>
                                    <div class="result-sub-small text-muted">${org.address}</div>
                                </div>
                            </a>`;
                    });
                }

                // 3. Отрисовка ВРАЧЕЙ (Doctor)
                if (data.doctors && data.doctors.length > 0) {
                    data.doctors.forEach(doctor => {
                        hasResults = true;
                        resultsContainer.innerHTML += `
                            <a href="/doctors/${doctor.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
                                <img src="${doctor.image}" class="search-img-thumb rounded-circle" alt="photo" style="object-fit: cover;">
                                <div class="ms-2">
                                    <div>
                                        <span class="result-title">🩺 ${doctor.name}</span>
                                        <span class="result-sub-muted small">(${doctor.specialization})</span>
                                    </div>
                                    <div class="result-sub-small text-muted">${doctor.clinic_name}</div>
                                </div>
                            </a>`;
                    });
                }

                // 4. Отрисовка СПЕЦИАЛИСТОВ (Specialist)
                if (data.specialists && data.specialists.length > 0) {
                    data.specialists.forEach(spec => {
                        hasResults = true;
                        resultsContainer.innerHTML += `
                            <a href="/specialists/${spec.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
                                <img src="${spec.image}" class="search-img-thumb rounded-circle" alt="photo" style="object-fit: cover;">
                                <div class="ms-2">
                                    <div>
                                        <span class="result-title">👤 ${spec.name}</span>
                                        <span class="result-sub-muted small">(${spec.specialization})</span>
                                    </div>
                                    <div class="result-sub-small text-muted">${spec.org_name}</div>
                                </div>
                            </a>`;
                    });
                }

                // Управление видимостью контейнера
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

    // Закрыть поиск при клике вне поля ввода или контейнера результатов
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.classList.add('d-none');
        }
    });

    // Показать результаты снова, если пользователь вернулся в поле (и там есть текст)
    searchInput.addEventListener('focus', function() {
        if (this.value.length >= 2 && resultsContainer.innerHTML !== '') {
            resultsContainer.classList.remove('d-none');
        }
    });
});