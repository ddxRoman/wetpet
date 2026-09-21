import { notify, notifyAfterReload } from '../notify';
import Choices from 'choices.js';
import 'choices.js/public/assets/styles/choices.min.css';

let isSubmitting = false;

function initAddDoctorScripts(modal) {
    console.log('Add Doctor modal initialized!');

    const form = modal.querySelector('#addDoctorForm');

    /* ===== БЛОК 1 — Начало практики не раньше 16 лет после рождения ===== */
    const dobInput = modal.querySelector('#date_of_birth');
    const practiceInput = modal.querySelector('#practice_started_at');

    if (dobInput && practiceInput) {
        dobInput.addEventListener('change', () => {
            const dob = new Date(dobInput.value);
            if (isNaN(dob)) return;
            const minDate = new Date(dob.getFullYear() + 16, dob.getMonth(), 1);
            const min = `${minDate.getFullYear()}-${String(minDate.getMonth() + 1).padStart(2, '0')}`;
            practiceInput.min = min;
            if (practiceInput.value && practiceInput.value < min) practiceInput.value = min;
        });
    }

    /* ===== БЛОК 2 — Регион → Город → Клиника ===== */
    const regionSelect = modal.querySelector('#regionSelect');
    const citySelect   = modal.querySelector('#citySelect');
    const clinicSelect = modal.querySelector('#clinicSelect');
    let regionChoices, cityChoices, clinicChoices;

    // Врач → список клиник (clinic_id), остальные специалисты → организации (organization_id).
    // По умолчанию форма отправляется в /specialist, поэтому стартуем в режиме специалиста.
    let isDoctorMode = false;
    let loadOrganizations = () => {};
    let syncAddressFields = () => {};

    if (regionSelect && citySelect && clinicSelect) {
        regionChoices = new Choices(regionSelect, { searchPlaceholderValue: 'Поиск...', shouldSort: false });
        cityChoices = new Choices(citySelect, { searchPlaceholderValue: 'Поиск...', shouldSort: false });
        clinicChoices = new Choices(clinicSelect, { searchPlaceholderValue: 'Поиск...', shouldSort: false });

        regionSelect.addEventListener('change', () => {
            const region = regionSelect.value;
            cityChoices.clearChoices();
            clinicChoices.clearChoices();
            cityChoices.setChoices([{ value: '', label: 'Выберите город', selected: true }], 'value', 'label', true);
            syncAddressFields();
            if (!region) return;
            fetch(`/api/cities/by-region/${encodeURIComponent(region)}`)
                .then(r => r.json())
                .then(list => {
                    cityChoices.setChoices(list.map(c => ({ value: c.id, label: c.name })), 'value', 'label', true);
                });
        });

        loadOrganizations = () => {
            const cityId = citySelect.value;
            clinicChoices.clearChoices();
            clinicChoices.setChoices([{ value: '', label: 'Выберите организацию', selected: true }], 'value', 'label', true);
            syncAddressFields();
            if (!cityId) return;
            const url = isDoctorMode
                ? `/api/clinics/by-city/${cityId}`
                : `/get-organizations-by-city-id/${cityId}`;
            fetch(url)
                .then(r => r.json())
                .then(list => {
                    clinicChoices.setChoices(list.map(c => ({ value: c.id, label: c.name })), 'value', 'label', true);
                });
        };

        citySelect.addEventListener('change', loadOrganizations);
    }

    /* Адрес частной практики: только для специалистов. Организация и частная практика
       могут быть указаны одновременно, поэтому выбор организации адрес не блокирует. */
    syncAddressFields = () => {
        const cols = modal.querySelectorAll('.private-address-col');
        cols.forEach(col => {
            col.classList.toggle('d-none', isDoctorMode);
            col.querySelectorAll('input').forEach(input => {
                input.disabled = isDoctorMode;
                if (isDoctorMode) input.value = '';
            });
        });
    };
    syncAddressFields();

    /* ===== БЛОК 3 — Сферы деятельности (с логикой смены Action) ===== */
    const fieldSelect = modal.querySelector('#fieldOfActivitySelect');
    if (fieldSelect) {
        fetch('/api/fields/specialists')
            .then(r => r.json())
            .then(list => {
                fieldSelect.innerHTML = '<option value="">Выберите сферу</option>';
                const doctors = list.filter(i => i.activity === 'doctor');
                const others  = list.filter(i => i.activity !== 'doctor');

                if (doctors.length) {
                    let group = document.createElement('optgroup');
                    group.label = "Врачи";
                    doctors.forEach(i => {
                        // Добавляем data-activity для определения типа
                        group.innerHTML += `<option value="${i.id}" data-activity="doctor">${i.name}</option>`;
                    });
                    fieldSelect.appendChild(group);
                }
                if (others.length) {
                    let group = document.createElement('optgroup');
                    group.label = "Другие специалисты";
                    others.forEach(i => {
                        group.innerHTML += `<option value="${i.id}" data-activity="specialist">${i.name}</option>`;
                    });
                    fieldSelect.appendChild(group);
                }
            });

        // Слушатель смены специальности для подмены URL формы
// Слушатель смены специальности для подмены URL формы
fieldSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const activity = selectedOption.getAttribute('data-activity');

    isDoctorMode = activity === 'doctor';

    if (isDoctorMode) {
        // Меняем на точный путь из web.php для сохранения доктора
        form.action = '/doctors/store';
    } else {
        // Меняем на точный путь из web.php для сохранения специалиста
        form.action = '/specialist';
    }

    // Врач привязывается к клинике, специалист — к организации
    if (clinicSelect) clinicSelect.name = isDoctorMode ? 'clinic_id' : 'organization_id';
    loadOrganizations();
    syncAddressFields();
});
    }

    /* ===== БЛОК 4 — ФОТО ===== */
    const picker = modal.querySelector('#doctorPhotoPicker');
    const fileInput = modal.querySelector('#doctorPhotoInput');
    const preview = modal.querySelector('#doctorPhotoPreview');
    const previewWrapper = modal.querySelector('#photoPreviewWrapper');
    const removeBtn = modal.querySelector('#removePhotoBtn');

    if (picker && fileInput) {
        picker.onclick = () => fileInput.click();
        fileInput.onchange = () => {
            const file = fileInput.files[0];
            if (!file) return;
            preview.src = URL.createObjectURL(file);
            previewWrapper.style.display = 'block';
            picker.style.display = 'none';
        };
        if (removeBtn) {
            removeBtn.onclick = () => {
                fileInput.value = '';
                preview.src = '';
                previewWrapper.style.display = 'none';
                picker.style.display = 'flex';
            };
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const modalElement = document.getElementById('addDoctorModal');
    const form = document.getElementById('addDoctorForm');
    const errorsBox = document.getElementById('doctorErrors');

    if (modalElement) initAddDoctorScripts(modalElement);

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (isSubmitting) return;

            isSubmitting = true;
            let redirected = false;
            if (errorsBox) errorsBox.classList.add('d-none');

            try {
                const formData = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();

                if (data.success) {
                    redirected = true;
                    notifyAfterReload(
                        data.message || (data.type === 'doctor' ? 'Ветеринар успешно добавлен' : 'Специалист успешно добавлен'),
                        'success'
                    );
                    // Сразу открываем карточку только что созданного специалиста / врача
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else {
                        window.location.reload();
                    }
                } else if (data.errors) {
                    if (errorsBox) {
                        errorsBox.innerHTML = Object.values(data.errors)
                            .map(e => `<div>${e[0]}</div>`).join('');
                        errorsBox.classList.remove('d-none');
                        modalElement.querySelector('.modal-body').scrollTop = 0;
                    }
                }
            } catch (err) {
                console.error('Ошибка:', err);
                notify('Не удалось сохранить. Попробуйте ещё раз.', 'error');
                if (errorsBox) {
                    errorsBox.innerText = 'Ошибка при сохранении.';
                    errorsBox.classList.remove('d-none');
                }
            } finally {
                if (!redirected) isSubmitting = false;
            }
        });
    }
});