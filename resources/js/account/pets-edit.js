import { showToast } from './toast';
import { initCropper } from './cropper-init';

// console.log('pets-edit.js loaded');

/* ======================================================
   🟢 ГЛОБАЛЬНЫЕ ПЕРЕМЕННЫЕ МОДУЛЯ
====================================================== */
// ✅ ДОБАВЛЕНО: защита от повторной инициализации cropper
let editCropperInitialized = false;

/* ======================================================
   🟢 ОТКРЫТИЕ МОДАЛКИ РЕДАКТИРОВАНИЯ
====================================================== */
export function openEditModal(petId) {
    const modal = document.getElementById('edit-pet-modal');
    const previewEdit = document.getElementById('edit-photo-preview');
    const photoInputEdit = document.getElementById('edit-pet-photo');
    const breedSelectEdit = document.getElementById('edit-pet-breed');

    // —————— Поиск карточки ——————
    const card = document.querySelector(`.pet-card[data-id="${petId}"]`);
    if (!card) {
        showToast('Питомец не найден на странице', 'error');
        return;
    }

    // —————— Считываем данные ——————
    const name  = card.querySelector('b')?.textContent?.trim() || '';
    const info  = card.querySelector('small')?.textContent?.trim() || '';
    const photo = card.querySelector('img')?.getAttribute('src') || '';

    const birth = card.dataset.birth || '';
    const death = card.dataset.death || '';
    const age   = card.dataset.age || '';

    // —————— Разбор вида и породы ——————
    let species = '';
    let breed   = '';

    if (info.includes('(')) {
        const [typePart, breedPart] = info.split('(');
        species = typePart.trim();
        breed   = breedPart.replace(')', '').trim();
    } else {
        species = info.trim();
    }

    // —————— Заполнение формы ——————
    document.getElementById('edit-pet-id').value   = petId;
    document.getElementById('edit-pet-name').value = name;

    const birthInput   = document.getElementById('edit-pet-birth');
    const ageInput     = document.getElementById('edit-pet-age');
    const unknownBirth = document.getElementById('edit-unknown-birth');
    const ageBlock     = document.getElementById('edit-age-block');

    birthInput.value = birth || '';
    document.getElementById('edit-pet-death').value = death;

    if (birth) {
        unknownBirth.checked = false;
        ageBlock.style.display = 'none';
        ageInput.value = '';
    } else {
        unknownBirth.checked = false;
        ageBlock.style.display = 'block';
        ageInput.value = age || '';
    }

    // —————— Фото ——————
    previewEdit.src = photo || '/storage/pets/default-pet.jpg';
    previewEdit.style.display = 'block';
    photoInputEdit.value = '';

    // —————— Кроппер ——————
    // 🔁 ИЗМЕНЕНО: инициализируем ТОЛЬКО один раз
    if (!editCropperInitialized) {
        initCropper(photoInputEdit, previewEdit);
        editCropperInitialized = true;
    }

    // —————— Породы ——————
    breedSelectEdit.innerHTML = '<option>Загрузка...</option>';

    if (!species) {
        showToast('Не удалось определить тип животного', 'error');
        return;
    }

    fetch(`/breeds?type=${encodeURIComponent(species)}`)
        .then(r => (r.ok ? r.json() : []))
        .then(breeds => {
            breedSelectEdit.innerHTML = '';

            if (!Array.isArray(breeds) || breeds.length === 0) {
                breedSelectEdit.innerHTML = '<option>Нет пород</option>';
                return;
            }

            breeds.forEach(b => {
                const selected =
                    b.name.toLowerCase() === breed.toLowerCase()
                        ? 'selected'
                        : '';
                breedSelectEdit.innerHTML +=
                    `<option value="${b.id}" ${selected}>${b.name}</option>`;
            });
        })
        .catch(() => {
            breedSelectEdit.innerHTML = '<option>Ошибка загрузки</option>';
            showToast('Ошибка при загрузке пород', 'error');
        });

    modal.style.display = 'flex';
}

/* ======================================================
   🟢 ОБЩАЯ ИНИЦИАЛИЗАЦИЯ (ОДИН DOMContentLoaded)
====================================================== */
document.addEventListener('DOMContentLoaded', () => {

    /* ===============================
       ➕ ДОБАВЛЕНИЕ ПИТОМЦА
    =============================== */
    const unknownBirth = document.getElementById('unknown-birth');
    const birthInput   = document.getElementById('pet-birth');
    const ageBlock     = document.getElementById('age-block');
    const ageInput     = document.getElementById('pet-age');

    function toggleBirthFields() {
        if (unknownBirth.checked) {
            birthInput.disabled = true;
            birthInput.value = '';
            ageBlock.style.display = 'block';
            ageInput.disabled = false;
        } else {
            birthInput.disabled = false;
            ageBlock.style.display = 'none';
            ageInput.value = '';
            ageInput.disabled = true;
        }
    }

    if (unknownBirth) {
        unknownBirth.checked = false;
        toggleBirthFields();
        unknownBirth.addEventListener('change', toggleBirthFields);
    }

    /* ===============================
       ✏️ РЕДАКТИРОВАНИЕ ПИТОМЦА
    =============================== */
    const editUnknownBirth = document.getElementById('edit-unknown-birth');
    const editBirthInput  = document.getElementById('edit-pet-birth');
    const editAgeBlock    = document.getElementById('edit-age-block');
    const editAgeInput    = document.getElementById('edit-pet-age');

    function toggleEditBirthFields() {
        if (editUnknownBirth.checked) {
            editBirthInput.disabled = true;
            editAgeBlock.style.display = 'block';
            editAgeInput.disabled = false;
        } else {
            editBirthInput.disabled = false;
            editAgeBlock.style.display = 'none';
            editAgeInput.value = '';
            editAgeInput.disabled = true;
        }
    }

    if (editUnknownBirth) {
        editUnknownBirth.checked = false;
        toggleEditBirthFields();
        editUnknownBirth.addEventListener('change', toggleEditBirthFields);
    }

    /* ===============================
       💾 СОХРАНЕНИЕ ИЗМЕНЕНИЙ
    =============================== */
    const modal        = document.getElementById('edit-pet-modal');
    const closeModal   = document.getElementById('close-modal');
    const saveEditBtn  = document.getElementById('save-edit-pet');
    const photoInput   = document.getElementById('edit-pet-photo');

    closeModal?.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    saveEditBtn?.addEventListener('click', async () => {
        const id = document.getElementById('edit-pet-id').value;
        const fd = new FormData();

        fd.append('name', document.getElementById('edit-pet-name').value);
        fd.append('animal_id', document.getElementById('edit-pet-breed').value);

        if (editUnknownBirth.checked) {
            fd.append('birth_date', '');
            fd.append('age', editAgeInput.value);
        } else {
            fd.append('birth_date', editBirthInput.value);
            fd.append('age', '');
        }

        // Пустое значение = «питомец жив» (дата смерти сбрасывается)
        fd.append('death_date', document.getElementById('edit-pet-death').value);

        fd.append('_method', 'PUT');

        if (photoInput.files.length > 0) {
            fd.append('photo', photoInput.files[0], 'pet.webp');
        }

        const token = document
            .querySelector('meta[name="csrf-token"]')
            ?.content || '';

        try {
            const res = await fetch(`/pets/${id}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                body: fd
            });

            const data = await res.json();

            if (res.ok && data.success) {
                showToast('Изменения сохранены', 'success');
                modal.style.display = 'none';
                setTimeout(() => location.reload(), 700);
            } else {
                showToast(data.message || 'Ошибка при сохранении', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Ошибка сети', 'error');
        }
    });
});
