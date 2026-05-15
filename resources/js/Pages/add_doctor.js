import Choices from 'choices.js';
import 'choices.js/public/assets/styles/choices.min.css';

let isSubmitting = false;

function initAddDoctorScripts(modal) {
    console.log('Add Doctor modal initialized!');

    const form = modal.querySelector('#addDoctorForm');

    /* ===== БЛОК 1 — Стаж от возраста ===== */
    const dobInput = modal.querySelector('#date_of_birth');
    const expInput = modal.querySelector('#experience');

    if (dobInput && expInput) {
        dobInput.addEventListener('change', () => {
            const dob = new Date(dobInput.value);
            if (isNaN(dob)) return;
            const now = new Date();
            const age = now.getFullYear() - dob.getFullYear() -
                ((now.getMonth() < dob.getMonth() || 
                (now.getMonth() === dob.getMonth() && now.getDate() < dob.getDate())) ? 1 : 0);
            const maxExperience = Math.max(age - 18, 0);
            expInput.max = maxExperience;
            if (+expInput.value > maxExperience) expInput.value = maxExperience;
        });
    }

    /* ===== БЛОК 2 — Регион → Город → Клиника ===== */
    const regionSelect = modal.querySelector('#regionSelect');
    const citySelect   = modal.querySelector('#citySelect');
    const clinicSelect = modal.querySelector('#clinicSelect');
    let regionChoices, cityChoices, clinicChoices;

    if (regionSelect && citySelect && clinicSelect) {
        regionChoices = new Choices(regionSelect, { searchPlaceholderValue: 'Поиск...', shouldSort: false });
        cityChoices = new Choices(citySelect, { searchPlaceholderValue: 'Поиск...', shouldSort: false });
        clinicChoices = new Choices(clinicSelect, { searchPlaceholderValue: 'Поиск...', shouldSort: false });

        regionSelect.addEventListener('change', () => {
            const region = regionSelect.value;
            cityChoices.clearChoices();
            clinicChoices.clearChoices();
            cityChoices.setChoices([{ value: '', label: 'Выберите город', selected: true }], 'value', 'label', true);
            if (!region) return;
            fetch(`/api/cities/by-region/${encodeURIComponent(region)}`)
                .then(r => r.json())
                .then(list => {
                    cityChoices.setChoices(list.map(c => ({ value: c.id, label: c.name })), 'value', 'label', true);
                });
        });

        citySelect.addEventListener('change', () => {
            const cityId = citySelect.value;
            clinicChoices.clearChoices();
            clinicChoices.setChoices([{ value: '', label: 'Выберите клинику', selected: true }], 'value', 'label', true);
            if (!cityId) return;
            fetch(`/api/clinics/by-city/${cityId}`)
                .then(r => r.json())
                .then(list => {
                    clinicChoices.setChoices(list.map(c => ({ value: c.id, label: c.name })), 'value', 'label', true);
                });
        });
    }

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

    if (activity === 'doctor') {
        // Меняем на точный путь из web.php для сохранения доктора
        form.action = '/doctors/store'; 
    } else {
        // Меняем на точный путь из web.php для сохранения специалиста
        form.action = '/specialist'; 
    }
    console.log('Action changed to:', form.action);
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

    /* ===== БЛОК 5 — Частный специалист ===== */
    const isPrivateCheckbox = modal.querySelector('#is_private');
    const addressSection = modal.querySelector('#address-section-add');
    if (isPrivateCheckbox && addressSection) {
        isPrivateCheckbox.addEventListener('change', function() {
            if (this.checked) {
                addressSection.style.display = 'block';
                if (clinicChoices) {
                    clinicChoices.setChoiceByValue('');
                    clinicChoices.disable();
                }
            } else {
                addressSection.style.display = 'none';
                if (clinicChoices) clinicChoices.enable();
            }
        });
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
                    alert('Успешно сохранено!');
                    window.location.reload();
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
                if (errorsBox) {
                    errorsBox.innerText = 'Ошибка при сохранении.';
                    errorsBox.classList.remove('d-none');
                }
            } finally {
                isSubmitting = false;
            }
        });
    }
});