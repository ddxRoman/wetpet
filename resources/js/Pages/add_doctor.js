import Choices from 'choices.js';
import 'choices.js/public/assets/styles/choices.min.css';

/* =====================================================================
   ГЛАВНАЯ ФУНКЦИЯ
===================================================================== */
function initAddDoctorScripts(modal) {
    console.log('Add Doctor modal initialized!');

    /* ===== БЛОК 1 — стаж от возраста ===== */
    const dobInput = modal.querySelector('#date_of_birth');
    const expInput = modal.querySelector('#experience');

    if (dobInput && expInput) {
        dobInput.addEventListener('change', () => {
            const dob = new Date(dobInput.value);
            if (isNaN(dob)) return;

            const now = new Date();
            const age =
                now.getFullYear() -
                dob.getFullYear() -
                ((now.getMonth() < dob.getMonth() ||
                    (now.getMonth() === dob.getMonth() && now.getDate() < dob.getDate()))
                    ? 1
                    : 0);

            const maxExperience = Math.max(age - 18, 0);
            expInput.max = maxExperience;
            if (+expInput.value > maxExperience) {
                expInput.value = maxExperience;
            }
        });
    }

    /* ===== БЛОК 2 — Region → City → Clinic ===== */
    const regionSelect = modal.querySelector('#regionSelect');
    const citySelect   = modal.querySelector('#citySelect');
    const clinicSelect = modal.querySelector('#clinicSelect');

    if (regionSelect && citySelect && clinicSelect) {

        const regionChoices = new Choices(regionSelect, {
            searchPlaceholderValue: 'Поиск региона...',
            shouldSort: false,
        });

        const cityChoices = new Choices(citySelect, {
            searchPlaceholderValue: 'Поиск города...',
            shouldSort: false,
        });

        const clinicChoices = new Choices(clinicSelect, {
            searchPlaceholderValue: 'Поиск клиники...',
            shouldSort: false,
        });

        /* ===== Регион → Город ===== */
        regionSelect.addEventListener('change', () => {
    const region = regionSelect.value;

    // ⬇️ сброс города (placeholder ТУТ)
    cityChoices.setChoices(
        [{ value: '', label: 'Выберите город', selected: true }],
        'value',
        'label',
        true
    );

    // ⬇️ сброс клиники
    clinicChoices.setChoices(
        [{ value: '', label: 'Сначала выберите город', selected: true }],
        'value',
        'label',
        true
    );

    if (!region) return;

    fetch(`/api/cities/by-region/${encodeURIComponent(region)}`)
        .then(r => r.json())
        .then(list => {
            // ⬇️ ЗДЕСЬ БЕЗ placeholder!
            cityChoices.setChoices(
                list.map(c => ({
                    value: c.id,
                    label: c.name
                })),
                'value',
                'label',
                true
            );
        });
});




        /* ===== Город → Клиника ===== */
citySelect.addEventListener('change', () => {
    const cityId = citySelect.value;

    // ⬇️ сброс клиники (placeholder ТУТ)
    clinicChoices.setChoices(
        [{ value: '', label: 'Выберите клинику', selected: true }],
        'value',
        'label',
        true
    );

    if (!cityId) return;

    fetch(`/api/clinics/by-city/${cityId}`)
        .then(r => r.json())
        .then(list => {
            // ⬇️ ЗДЕСЬ БЕЗ placeholder!
            clinicChoices.setChoices(
                list.map(c => ({
                    value: c.id,
                    label: c.name
                })),
                'value',
                'label',
                true
            );
        });
});


    }

    /* ===== БЛОК 3 — сферы ===== */
    const fieldSelect = modal.querySelector('#fieldOfActivitySelect');

if (fieldSelect) {
fetch('/api/fields/specialists')

        .then(r => r.json())
        .then(list => {
            fieldSelect.innerHTML = '<option value="">Выберите сферу</option>';

            const doctors = list.filter(i => i.activity === 'doctor');
            const others  = list.filter(i => i.activity !== 'doctor');

            if (doctors.length) {
                fieldSelect.innerHTML += '<optgroup label="Врачи">';
                doctors.forEach(i => {
                    fieldSelect.innerHTML += `<option value="${i.id}">${i.name}</option>`;
                });
                fieldSelect.innerHTML += '</optgroup>';
            }

            if (others.length) {
                fieldSelect.innerHTML += '<optgroup label="Другие специалисты">';
                others.forEach(i => {
                    fieldSelect.innerHTML += `<option value="${i.id}">${i.name}</option>`;
                });
                fieldSelect.innerHTML += '</optgroup>';
            }
        })
        .catch(() => {
            fieldSelect.innerHTML = `<option value="">Ошибка загрузки</option>`;
        });
}

    /* ===== БЛОК 4 — форма ===== */
    const form = modal.querySelector('#addDoctorForm');
    const errBox = modal.querySelector('#doctorErrors');

    if (form) {
        form.addEventListener('submit', async e => {
            e.preventDefault();

            const res = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json' },
            });

            const json = await res.json();

            if (json.errors) {
                errBox.innerHTML = Object.values(json.errors)
                    .map(e => `<div>${e[0]}</div>`)
                    .join('');
                errBox.classList.remove('d-none');
                return;
            }

            location.reload();
        });
    }

    /* ===== БЛОК 5 — фото ===== */
const picker = modal.querySelector('#photoPicker');
const fileInput = modal.querySelector('#doctorPhotoInput');
const preview = modal.querySelector('#doctorPhotoPreview');
const previewWrapper = modal.querySelector('#photoPreviewWrapper');
const removeBtn = modal.querySelector('#removePhotoBtn');

if (picker && fileInput && preview && removeBtn) {

    picker.onclick = () => fileInput.click();

    fileInput.onchange = () => {
        const file = fileInput.files[0];
        if (!file) return;

        preview.src = URL.createObjectURL(file);
        previewWrapper.style.display = 'block';
        picker.style.display = 'none';
    };

    removeBtn.onclick = () => {
        fileInput.value = '';              // 🔥 главное
        preview.src = '';
        previewWrapper.style.display = 'none';
        picker.style.display = 'flex';
    };
}

}

/* =====================================================================
   ИНИЦИАЛИЗАЦИЯ
===================================================================== */
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('addDoctorModal');
    if (modal) initAddDoctorScripts(modal);
});


document.addEventListener('DOMContentLoaded', () => {
    const fieldSelect  = document.getElementById('fieldOfActivitySelect');
    const citySelect   = document.getElementById('citySelect');
    const clinicSelect = document.getElementById('clinicSelect');

    function loadClinics() {
        const fieldId = fieldSelect.value;
        const cityId  = citySelect.value;

        clinicSelect.innerHTML = '<option>Загрузка...</option>';

        if (!fieldId || !cityId) {
            clinicSelect.innerHTML = '<option>Сначала выберите город и сферу деятельности</option>';
            return;
        }

        fetch(`/ajax/organizations?field_of_activity_id=${fieldId}&city_id=${cityId}`)
            .then(res => res.json())
            .then(data => {
                clinicSelect.innerHTML = '';

                if (!data.length) {
                    clinicSelect.innerHTML = '<option>Организации не найдены</option>';
                    return;
                }

                data.forEach(org => {
                    clinicSelect.innerHTML += `<option value="${org.id}">${org.name}</option>`;
                });
            })
            .catch(() => {
                clinicSelect.innerHTML = '<option>Ошибка загрузки</option>';
            });
    }

    fieldSelect.addEventListener('change', loadClinics);
    citySelect.addEventListener('change', loadClinics);
});

