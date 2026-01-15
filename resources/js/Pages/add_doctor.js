import Choices from 'choices.js';
import 'choices.js/public/assets/styles/choices.min.css';

let isSubmitting = false;

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

    /* ===== БЛОК 2 — Region → City → Clinic (Choices) ===== */
    const regionSelect = modal.querySelector('#regionSelect');
    const citySelect   = modal.querySelector('#citySelect');
    const clinicSelect = modal.querySelector('#clinicSelect');

    let regionChoices, cityChoices, clinicChoices;

    if (regionSelect && citySelect && clinicSelect) {

        regionChoices = new Choices(regionSelect, {
            searchPlaceholderValue: 'Поиск региона...',
            shouldSort: false,
        });

        cityChoices = new Choices(citySelect, {
            searchPlaceholderValue: 'Поиск города...',
            shouldSort: false,
        });

        clinicChoices = new Choices(clinicSelect, {
            searchPlaceholderValue: 'Поиск клиники...',
            shouldSort: false,
        });

        regionSelect.addEventListener('change', () => {
            const region = regionSelect.value;

            cityChoices.clearChoices();
            clinicChoices.clearChoices();

            cityChoices.setChoices(
                [{ value: '', label: 'Выберите город', selected: true }],
                'value',
                'label',
                true
            );

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

        citySelect.addEventListener('change', () => {
            const cityId = citySelect.value;

            clinicChoices.clearChoices();
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

    /* ===== БЛОК 4 — ФОТО ===== */
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
            fileInput.value = '';
            preview.src = '';
            previewWrapper.style.display = 'none';
            picker.style.display = 'flex';
        };
    }
}

/* =====================================================================
   SUBMIT (ЕДИНЫЙ, БЕЗ КОНФЛИКТОВ)
===================================================================== */
document.addEventListener('DOMContentLoaded', () => {

    const modal = document.getElementById('addDoctorModal');
    const form  = document.getElementById('addDoctorForm');
    const errorsBox = document.getElementById('doctorErrors');

    if (modal) initAddDoctorScripts(modal);
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (isSubmitting) return;

        isSubmitting = true;
        errorsBox.classList.add('d-none');

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            });

            const data = await res.json();

            if (data.success) {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(modal);
                modalInstance.hide();

                form.reset();
                alert('Специалист успешно добавлен');
            } else if (data.errors) {
                errorsBox.innerHTML = Object.values(data.errors)
                    .map(e => `<div>${e[0]}</div>`)
                    .join('');
                errorsBox.classList.remove('d-none');
            } else {
                throw new Error();
            }
        } catch {
            errorsBox.innerText = 'Ошибка сервера';
            errorsBox.classList.remove('d-none');
        } finally {
            isSubmitting = false;
        }
    });
});
