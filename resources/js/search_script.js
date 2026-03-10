document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('clinic-live-search');
    const resultsContainer = document.getElementById('search-results');

    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        const query = this.value;

        if (query.length < 2) {
            resultsContainer.classList.add('d-none');
            return;
        }

        fetch(`/api/clinics-search?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
// ... внутри fetch ...
.then(data => {
    resultsContainer.innerHTML = '';
    let hasResults = false;

    // Сначала выводим клиники
// ... внутри fetch ...
data.clinics.forEach(clinic => {
    hasResults = true;
    resultsContainer.innerHTML += `
        <a href="/clinics/${clinic.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
            <img src="${clinic.image}" class="search-img-thumb" alt="logo">
            <div class="ms-2">
                <div class="result-title">${clinic.name}</div>
                <div class="result-sub-small">${clinic.address}</div>
            </div>
        </a>`;
});



    // Затем выводим врачей
data.doctors.forEach(doctor => {
    hasResults = true;
    resultsContainer.innerHTML += `
        <a href="/doctors/${doctor.slug}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
            <img src="${doctor.image}" class="search-img-thumb rounded-circle" alt="photo">
            <div class="ms-2">
                <div>
                    <span class="result-title">${doctor.name}</span>
                    <span class="result-sub-muted">(${doctor.specialization})</span>
                </div>
                <div class="result-sub-small">${doctor.clinic_name}</div>
            </div>
        </a>`;
});

    if (hasResults) {
        resultsContainer.classList.remove('d-none');
    } else {
        resultsContainer.classList.add('d-none');
    }
});
    });

    // Закрыть поиск при клике вне его
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.classList.add('d-none');
        }
    });
});