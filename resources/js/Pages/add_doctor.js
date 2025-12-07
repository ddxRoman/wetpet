document.addEventListener("DOMContentLoaded", () => {
    const fileInput = document.getElementById("doctorPhotoInput");
    const preview = document.getElementById("doctorPhotoPreview");
    const picker = document.getElementById("photoPicker");

    if (!fileInput || !preview || !picker) {
        console.error("Photo elements not found:", { fileInput, preview, picker });
        return;
    }

    // Клик по квадрату вызывает выбор файла
    picker.addEventListener("click", () => fileInput.click());

    // Клик по превью – тоже вызывает выбор файла (возможность изменить картинку)
    preview.addEventListener("click", () => fileInput.click());

    // helper: показать превью из File или Blob
    function showPreviewFromFile(file) {
        if (!file) return;
        try {
            const url = URL.createObjectURL(file);
            preview.src = url;
            preview.style.display = "block";
            picker.style.display = "none";
        } catch (err) {
            console.error("showPreviewFromFile error:", err);
        }
    }

    // fallback на случай отсутствия кропера
    function fallbackAttach() {
        fileInput.addEventListener("change", () => {
            const f = fileInput.files && fileInput.files[0];
            if (!f) {
                preview.style.display = "none";
                picker.style.display = "flex";
                return;
            }
            showPreviewFromFile(f);
        });
    }

    // Подключаем кроппер
    try {
        if (typeof initCropper === 'function') {
            initCropper(fileInput, preview);

            fileInput.addEventListener("change", () => {
                const f = fileInput.files && fileInput.files[0];
                if (f) {
                    showPreviewFromFile(f);
                } else {
                    preview.style.display = "none";
                    picker.style.display = "flex";
                }
            });

            console.info("initCropper found and initialized.");
        } else {
            console.warn("initCropper() not found — using fallback preview only.");
            fallbackAttach();
        }
    } catch (err) {
        console.error("Error initializing cropper:", err);
        fallbackAttach();
    }

    // двойной клик по превью — сброс изображения
    preview.addEventListener('dblclick', () => {
        preview.src = '';
        preview.style.display = 'none';
        picker.style.display = 'flex';
        fileInput.value = '';
    });
});

document.addEventListener("DOMContentLoaded", () => {

    const citySelect = document.getElementById("citySelect");
    const clinicSelect = document.getElementById("clinicSelect");

    let clinicChoices = null;

    // подключаем Choices.js
    function initClinicChoices() {
        if (clinicChoices) clinicChoices.destroy();
        clinicChoices = new Choices("#clinicSelect", {
            searchPlaceholderValue: "Поиск клиники...",
            removeItemButton: true
        });
    }

    initClinicChoices();

    // загрузка клиник по выбранному городу
    citySelect.addEventListener("change", () => {
        const cityId = citySelect.value;

        clinicSelect.innerHTML = `<option value="">Загрузка...</option>`;
        clinicChoices.destroy();

        if (!cityId) {
            clinicSelect.innerHTML = `<option value="">Сначала выберите город</option>`;
            initClinicChoices();
            return;
        }

        fetch(`/api/clinics/by-city/${cityId}`)
            .then(r => r.json())
            .then(data => {
                clinicSelect.innerHTML = `<option value="">Выберите клинику</option>`;

                data.forEach(clinic => {
                    const opt = document.createElement("option");
                    opt.value = clinic.id;
                    opt.textContent = clinic.name;
                    clinicSelect.appendChild(opt);
                });

                initClinicChoices();

            })
            .catch(err => {
                console.error(err);
                clinicSelect.innerHTML = `<option value="">Ошибка загрузки</option>`;
                initClinicChoices();
            });
    });

});
document.addEventListener("DOMContentLoaded", () => {

    const fieldSelect = document.getElementById("fieldOfActivitySelect");

    if (fieldSelect) {
        fetch('/api/fields/specialists')
            .then(r => r.json())
            .then(data => {
                fieldSelect.innerHTML = `<option value="">Выберите сферу деятельности</option>`;

                data.forEach(item => {
                    const opt = document.createElement("option");
                    opt.value = item.id;
                    opt.textContent = item.name;
                    fieldSelect.appendChild(opt);
                });
            })
            .catch(err => {
                console.error("Ошибка загрузки полей деятельности:", err);
                fieldSelect.innerHTML = `<option value="">Ошибка загрузки</option>`;
            });
    }

});
document.getElementById('addDoctorForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const form = e.target;
    const data = new FormData(form);

    const res = await fetch(form.action, {
        method: 'POST',
            headers: {
        'Accept': 'application/json'
    },
        body: data
    });

    const json = await res.json();

    const errBox = document.getElementById('doctorErrors');

if (json.errors) {

    // очистка подсветки
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    let html = '';
    for (let field in json.errors) {
        html += `<div>${json.errors[field][0]}</div>`;

        // подсветка нужного поля
        const input = document.querySelector(`[name="${field}"]`);
        if (input) input.classList.add('is-invalid');
    }

    errBox.innerHTML = html;
    errBox.classList.remove('d-none');

    errBox.scrollIntoView({ behavior: "smooth" });

    return;
}

if (json.errors) {
    let html = '';
    Object.values(json.errors).forEach(err => {
        html += `<div>${err}</div>`;
    });

    errBox.innerHTML = html;
    errBox.classList.remove('d-none');
    return;
}


    // Ошибки убираем
    errBox.classList.add('d-none');
    errBox.innerHTML = '';

    // Если успех
    alert(json.message);
    location.reload();
});
