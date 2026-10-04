import { notify, notifyAfterReload } from '../notify';
import Choices from 'choices.js';
import { enableAddCity, applyNewCity } from './city-add-option';
import { askDuplicate } from './duplicate-popup';
import 'choices.js/public/assets/styles/choices.min.css';

import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

// console.log('add_organization.js loaded');

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('addOrganizationModal');
    if (!modal) return;

    let initialized = false;

    modal.addEventListener('shown.bs.modal', () => {
        if (initialized) return;
        initialized = true;
        initAddOrganizationModal(modal);
    });
});

/* ============================================================================
   ОСНОВНАЯ ЛОГИКА МОДАЛКИ
============================================================================ */
function initAddOrganizationModal(modal) {

    /* ===== Choices helper ===== */
    function initChoices(select, opts = {}) {
        if (!select) return null;
        if (select._choices) select._choices.destroy();

        select._choices = new Choices(select, {
            searchPlaceholderValue: 'Поиск...',
            removeItemButton: true,
            shouldSort: false,
            ...opts
        });

        return select._choices;
    }

    /* ===== Elements ===== */
    const form = modal.querySelector('#addOrganizationForm');
    const errBox = modal.querySelector('#orgErrors');

    const fieldSelect  = modal.querySelector('#fieldOfActivitySelect');
    const regionSelect = modal.querySelector('#regionSelect');
    const citySelect   = modal.querySelector('#citySelect');
    const clinicSelect = modal.querySelector('#clinicSelect');

    /* ===== Choices init ===== */
    initChoices(regionSelect, { searchPlaceholderValue: 'Поиск региона...' });
    initChoices(citySelect,   { searchPlaceholderValue: 'Поиск города...' });

    // Нет своего города в списке — кнопка «Добавить этот населённый пункт» прямо в выпадающем списке
    enableAddCity({
        select: citySelect,
        choices: citySelect?._choices,
        regionSelect,
        getKnownNames: () => [...(citySelect?.options || [])].map(o => o.text),
    });

    if (clinicSelect) initChoices(clinicSelect, { searchPlaceholderValue: 'Поиск клиники...' });

    /* ===== Сферы деятельности ===== */
    if (fieldSelect) {
        fetch('/api/fields/vetclinic')
            .then(r => r.json())
            .then(list => {
                fieldSelect.innerHTML = `<option value="">Выберите сферу</option>`;
                list.forEach(i => {
                    fieldSelect.innerHTML += `<option value="${i.id}">${i.name}</option>`;
                });
            })
            .catch(() => {
                fieldSelect.innerHTML = `<option value="">Ошибка загрузки</option>`;
            });
    }

    /* ===== Region → City ===== */
    if (regionSelect && citySelect) {
        regionSelect.addEventListener('change', () => {
            const region = regionSelect.value;

            citySelect.innerHTML = `<option value="">Загрузка...</option>`;
            citySelect._choices?.setChoices([{ value:'', label:'Загрузка...' }], 'value', 'label', true);

            if (!region) return;

            fetch(`/api/cities/by-region/${encodeURIComponent(region)}`)
                .then(r => r.json())
                .then(list => {
                    citySelect.innerHTML = `<option value="">Выберите город</option>`;
                    list.forEach(c => {
                        citySelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                    });

                    citySelect._choices.setChoices(
                        [...citySelect.options].map(o => ({ value:o.value, label:o.text })),
                        'value', 'label', true
                    );
                })
                .catch(() => {
                    citySelect.innerHTML = `<option value="">Ошибка</option>`;
                });
        });
    }

    /* ===== City → Clinics (если есть) ===== */
    if (citySelect && clinicSelect) {
        citySelect.addEventListener('change', () => {
            const cityId = citySelect.value;
            clinicSelect.innerHTML = `<option value="">Загрузка...</option>`;

            // У нового (ещё не сохранённого) города id нет — клиник в нём пока нет
            if (!cityId || !/^\d+$/.test(cityId)) {
                clinicSelect.innerHTML = `<option value="">Выберите клинику</option>`;
                clinicSelect._choices?.setChoices(
                    [{ value:'', label:'Выберите клинику' }], 'value', 'label', true
                );
                return;
            }

            fetch(`/api/clinics/by-city/${cityId}`)
                .then(r => r.json())
                .then(list => {
                    clinicSelect.innerHTML = `<option value="">Выберите клинику</option>`;
                    list.forEach(c => {
                        clinicSelect.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                    });

                    clinicSelect._choices.setChoices(
                        [...clinicSelect.options].map(o => ({ value:o.value, label:o.text })),
                        'value', 'label', true
                    );
                });
        });
    }

    /* ===== Logo / Cropper ===== */
const picker  = document.getElementById('orgPhotoPicker');
const input   = document.getElementById('orgPhotoInput');
const preview = document.getElementById('orgPhotoPreview');
const wrapper = document.getElementById('orgPhotoPreviewWrapper');
const remove  = document.getElementById('orgRemovePhotoBtn');

picker.addEventListener('click', () => input.click());

input.addEventListener('change', () => {
    const file = input.files[0];
    if (!file) return;

    preview.src = URL.createObjectURL(file);
    wrapper.style.display = 'block';
    picker.style.display = 'none';
});

remove.addEventListener('click', () => {
    input.value = '';
    preview.src = '';
    wrapper.style.display = 'none';
    picker.style.display = 'flex';
});


    /* ===== AJAX submit ===== */
if (form) {
    let isSubmitting = false;
    form.addEventListener('submit', async e => {
        e.preventDefault();
        if (isSubmitting) return; // защита от повторной отправки
        isSubmitting = true;
        let redirected = false;
        errBox?.classList.add('d-none');
        if (errBox) errBox.innerHTML = '';

        try {
            const post = async (body) => {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body
                });
                return { res: response, json: await response.json() };
            };

            const formData = applyNewCity(form, new FormData(form));
            let { res, json } = await post(formData);

            // Сервер нашёл похожую клинику/организацию: «Это она?»
            if (res.status === 409 && json.duplicates) {
                const answer = await askDuplicate({ items: json.duplicates });

                if (answer.action === 'yes' && answer.item?.url) {
                    redirected = true;
                    window.location.href = answer.item.url;   // «Да» — открываем найденную карточку
                    return;
                }
                if (answer.action !== 'no') return;            // окно закрыли — остаёмся в форме

                formData.set('skip_duplicates', '1');          // «Нет» — создаём новую
                ({ res, json } = await post(formData));
            }

            if (res.status === 422) { // Ошибка валидации Laravel
                if (errBox) {
                    errBox.innerHTML = Object.values(json.errors)
                        .flat()
                        .map(e => `<div>${e}</div>`).join('');
                    errBox.classList.remove('d-none');
                }
                return; // ВАЖНО: выходим, чтобы не было релоада
            }

            if (json.success) {
                redirected = true;
                notifyAfterReload(json.message || 'Организация успешно добавлена!', 'success');
                // Сразу открываем карточку только что созданной клиники / организации
                if (json.redirect_url) {
                    window.location.href = json.redirect_url;
                } else {
                    location.reload();
                }
            } else {
                console.error('Server error:', json);
                notify('Не удалось сохранить. Попробуйте ещё раз.', 'error');
            }
        } catch (error) {
            console.error('Fetch error:', error);
            notify('Не удалось сохранить. Попробуйте ещё раз.', 'error');
        } finally {
            if (!redirected) isSubmitting = false;
        }
    });
}
}