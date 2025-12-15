import Choices from 'choices.js';
import 'choices.js/public/assets/styles/choices.min.css';

// console.log('add_doctor.js loaded');

/* ============================================================================
   ГЛАВНАЯ ФУНКЦИЯ
============================================================================ */
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

    /* ===== БЛОК 2 — клиники ===== */
    const citySelect = modal.querySelector('#citySelect');
    const clinicSelect = modal.querySelector('#clinicSelect');
    let clinicChoices;

    function initClinicChoices() {
        if (clinicChoices) clinicChoices.destroy();
        clinicChoices = new Choices(clinicSelect, {
            searchPlaceholderValue: 'Поиск клиники...',
            removeItemButton: true,
        });
    }

    if (citySelect && clinicSelect) {
        initClinicChoices();

        citySelect.addEventListener('change', () => {
            const cityId = citySelect.value;

            clinicChoices.destroy();
            clinicSelect.innerHTML = `<option value="">Загрузка...</option>`;

            if (!cityId) {
                clinicSelect.innerHTML = `<option value="">Сначала выберите город</option>`;
                initClinicChoices();
                return;
            }

            fetch(`/api/clinics/by-city/${cityId}`)
                .then(r => r.json())
                .then(list => {
                    clinicSelect.innerHTML = `<option value="">Выберите клинику</option>`;
                    list.forEach(c => {
                        clinicSelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                    });
                    initClinicChoices();
                })
                .catch(() => {
                    clinicSelect.innerHTML = `<option value="">Ошибка загрузки</option>`;
                    initClinicChoices();
                });
        });
    }

    /* ===== БЛОК 3 — сферы ===== */
    const fieldSelect = modal.querySelector('#fieldOfActivitySelect');

    if (fieldSelect) {
        fetch('/api/fields/specialists')
            .then(r => r.json())
            .then(list => {
                fieldSelect.innerHTML = `<option value="">Выберите сферу</option>`;
                list.forEach(i => {
                    fieldSelect.innerHTML += `<option value="${i.id}">${i.name}</option>`;
                });
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

    if (picker && fileInput && preview) {
        picker.onclick = () => fileInput.click();
        fileInput.onchange = () => {
            const f = fileInput.files[0];
            if (!f) return;
            preview.src = URL.createObjectURL(f);
            preview.style.display = 'block';
            picker.style.display = 'none';
        };
    }
}

/* ============================================================================
   ИНИЦИАЛИЗАЦИЯ
============================================================================ */
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('addDoctorModal');
    if (modal) initAddDoctorScripts(modal);
});
