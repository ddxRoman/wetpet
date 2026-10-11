import { showToast } from './toast';
import { initCropper } from './cropper-init';

// console.log('pets-edit.js loaded');

/* ======================================================
   🟢 ГЛОБАЛЬНЫЕ ПЕРЕМЕННЫЕ МОДУЛЯ
====================================================== */
// ✅ ДОБАВЛЕНО: защита от повторной инициализации cropper
let editCropperInitialized = false;

/* ======================================================
   🖼 ГАЛЕРЕЯ ПИТОМЦА (Премиум)
   Данные о доп. фото приходят из pets.js вместе со списком питомцев
====================================================== */
const galleryStore = new Map(); // petId -> { photos: [{id, url}], hasMain: bool, caption: string }

export function registerPetGallery(petId, data) {
    galleryStore.set(String(petId), data);
}

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function renderGallery(petId) {
    const data = galleryStore.get(String(petId)) || { photos: [], hasMain: false };
    const premium = Boolean(window.userPremium);
    const limit = Number(window.petPhotoLimit) || 1;
    const total = (data.hasMain ? 1 : 0) + data.photos.length;

    const list = document.getElementById('edit-gallery-list');
    const counter = document.getElementById('edit-gallery-counter');
    const premiumBox = document.getElementById('edit-gallery-premium');
    const upsell = document.getElementById('edit-gallery-upsell');
    const addBtn = document.getElementById('edit-gallery-add');
    if (!list) return;

    counter.textContent = `(${total} из ${limit})`;

    list.innerHTML = '';
    data.photos.forEach(ph => {
        const box = document.createElement('div');
        box.style.cssText = 'position:relative; width:72px; height:72px;';
        box.innerHTML = `
            <img src="${ph.url}" alt="Фото" style="width:72px; height:72px; object-fit:cover; border-radius:8px; border:1px solid #ddd;">
            <button type="button" aria-label="Удалить фото"
                style="position:absolute; top:-6px; right:-6px; width:22px; height:22px; border:0; border-radius:50%; background:#d64545; color:#fff; line-height:1; cursor:pointer;">&times;</button>`;
        box.querySelector('button').addEventListener('click', () => deleteGalleryPhoto(petId, ph.id));
        list.appendChild(box);
    });

    // Загружать можно только с Премиум и пока не достигнут лимит
    premiumBox.style.display = premium ? 'block' : 'none';
    upsell.style.display = premium ? 'none' : 'block';
    addBtn.style.display = premium && total < limit ? 'inline-block' : 'none';
}

async function uploadGalleryPhotos(petId, files) {
    if (!files.length) return;

    const fd = new FormData();
    files.forEach(f => fd.append('photos[]', f));

    try {
        const res = await fetch(`/pets/${petId}/photos`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
            credentials: 'same-origin',
            body: fd
        });
        const data = await res.json().catch(() => ({}));

        if (res.ok && data.success) {
            const entry = galleryStore.get(String(petId)) || { photos: [], hasMain: false };
            entry.photos = data.photos;
            galleryStore.set(String(petId), entry);
            renderGallery(petId);
            showToast('Фото добавлены', 'success');
        } else {
            const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
            showToast(firstError || data.message || 'Не удалось загрузить фото', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Ошибка сети', 'error');
    }
}

async function deleteGalleryPhoto(petId, photoId) {
    if (!confirm('Удалить фото?')) return;

    try {
        const res = await fetch(`/pet-photos/${photoId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        const data = await res.json().catch(() => ({}));

        if (res.ok && data.success) {
            const entry = galleryStore.get(String(petId)) || { photos: [], hasMain: false };
            entry.photos = data.photos;
            galleryStore.set(String(petId), entry);
            renderGallery(petId);
        } else {
            showToast(data.message || 'Не удалось удалить фото', 'error');
        }
    } catch (err) {
        console.error(err);
        showToast('Ошибка сети', 'error');
    }
}

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

    // —————— Галерея и подпись (Премиум) ——————
    renderGallery(petId);
    const captionBox = document.getElementById('edit-pet-caption-box');
    if (captionBox) {
        captionBox.style.display = window.userPremium ? 'block' : 'none';
        document.getElementById('edit-pet-caption').value =
            (galleryStore.get(String(petId)) || {}).caption || '';
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

    // Галерея: загрузка новых фото сразу после выбора файлов
    const galleryInput = document.getElementById('edit-gallery-input');
    galleryInput?.addEventListener('change', async () => {
        const id = document.getElementById('edit-pet-id').value;
        const files = Array.from(galleryInput.files);
        galleryInput.value = '';
        await uploadGalleryPhotos(id, files);
    });

    // Ссылка «Премиум» в модалке: закрываем модалку и открываем вкладку
    document.getElementById('edit-gallery-go-premium')?.addEventListener('click', e => {
        e.preventDefault();
        modal.style.display = 'none';
        document.querySelector('.tab-btn[data-tab="premium"]')?.click();
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

        if (window.userPremium) {
            fd.append('caption', document.getElementById('edit-pet-caption').value);
        }

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
