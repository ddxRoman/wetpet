// ------------------------------
// Region → City dynamic loading
// ------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const regionSelect = document.getElementById('regionSelect');
    const citySelect = document.getElementById('citySelect');

    if (regionSelect) {
        regionSelect.addEventListener('change', function () {
            const region = this.options[this.selectedIndex].text.trim();

            citySelect.innerHTML = '<option value="">Загрузка...</option>';

            fetch(`/api/cities?region=${encodeURIComponent(region)}`)
                .then(res => res.json())
                .then(data => {
                    citySelect.innerHTML = '<option value="">Выберите город</option>';
                    data.forEach(city => {
                        citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                    });
                })
                .catch(() => {
                    citySelect.innerHTML = '<option value="">Ошибка загрузки</option>';
                });
        });
    }

    // ------------------------------
    // Image preview
    // ------------------------------
    const fileInput = document.getElementById('fileInput');
    const preview = document.getElementById('preview');

    if (fileInput && preview) {
        fileInput.addEventListener('change', function () {
            preview.innerHTML = '';
            [...this.files].forEach(file => {
                let reader = new FileReader();
                reader.onload = e => {
                    preview.innerHTML += `
                        <div class="preview-item">
                            <img src="${e.target.result}" alt="">
                        </div>`;
                };
                reader.readAsDataURL(file);
            });
        });
    }

    // ------------------------------
    // Autocomplete fields
    // ------------------------------
    function setupAutocomplete(inputId, listId) {
        const input = document.getElementById(inputId);
        const list = document.getElementById(listId);

        if (!input || !list) return;

        input.addEventListener('input', () => {
            const q = input.value.trim();
            if (q.length < 2) {
                list.innerHTML = '';
                return;
            }

            fetch(`/api/autocomplete?q=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(data => {
                    list.innerHTML = '';
                    data.forEach(item => {
                        list.innerHTML += `<div class="autocomplete-item" data-value="${item}">${item}</div>`;
                    });
                });
        });

        list.addEventListener('click', (e) => {
            if (e.target.classList.contains('autocomplete-item')) {
                input.value = e.target.dataset.value;
                list.innerHTML = '';
            }
        });

        document.addEventListener('click', e => {
            if (!list.contains(e.target) && !input.contains(e.target)) {
                list.innerHTML = '';
            }
        });
    }

    // Подключаем автокомплиты
    setupAutocomplete('activityInput', 'activityList');
    setupAutocomplete('serviceInput', 'serviceList');
});
