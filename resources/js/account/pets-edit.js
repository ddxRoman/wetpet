import { showToast } from './toast';
import { initCropper } from './cropper-init';

// =========================
//  ОТКРЫТИЕ МОДАЛКИ
// =========================
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
    const name = card.querySelector('b')?.textContent?.trim() || '';
    const info = card.querySelector('small')?.textContent?.trim() || '';
    const photo = card.querySelector('img')?.getAttribute('src') || '';

    // 🔹 Дата рождения
const birth = card.getAttribute("data-birth") || "";


// 🔹 Возраст (если нет даты)
const age   = card.getAttribute("data-age") || "";


    // —————— Разбор типа и породы ——————
    let species = '', breed = '';
    if (info.includes('(')) {
        const [typePart, breedPart] = info.split('(');
        species = typePart.trim();
        breed = breedPart.replace(')', '').trim();
    } else {
        species = info.trim();
    }

    // —————— Заполнение модалки ——————
    document.getElementById('edit-pet-id').value = petId;
    document.getElementById('edit-pet-name').value = name;

    // 🔥 Автоматическое заполнение даты рождения
    document.getElementById('edit-pet-birth').value = birth || '';

    if (birth) {
        // Если дата есть — отключаем режим возраста
        document.getElementById('edit-unknown-birth').checked = false;
        document.getElementById('edit-age-block').style.display = 'none';
        document.getElementById('edit-pet-age').value = '';
    } else {
        // Если даты нет — включаем возраст
        document.getElementById('edit-unknown-birth').checked = false;
        document.getElementById('edit-age-block').style.display = 'block';
        document.getElementById('edit-pet-age').value = age || '';
    }

    // —————— Фото ——————
    previewEdit.src = photo || '/storage/pets/default-pet.jpg';
    previewEdit.style.display = 'block';

    // —————— Кроппер ——————
    photoInputEdit.value = '';
    initCropper(photoInputEdit, previewEdit);

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
                const selected = b.name.toLowerCase() === breed.toLowerCase() ? 'selected' : '';
                breedSelectEdit.innerHTML += `<option value="${b.id}" ${selected}>${b.name}</option>`;
            });
        })
        .catch(() => {
            breedSelectEdit.innerHTML = '<option>Ошибка загрузки</option>';
            showToast('Ошибка при загрузке списка пород', 'error');
        });

    modal.style.display = 'flex';
}


document.addEventListener("DOMContentLoaded", () => {

    // === Добавление питомца ===
    const unknownBirth = document.getElementById("unknown-birth");
    const birthInput = document.getElementById("pet-birth");
    const ageBlock = document.getElementById("age-block");
    const ageInput = document.getElementById("pet-age");

    function toggleBirthFields() {
        if (unknownBirth.checked) {
            birthInput.disabled = true;
            birthInput.value = "";
            ageBlock.style.display = "block";
            ageInput.disabled = false;
        } else {
            birthInput.disabled = false;
            ageBlock.style.display = "none";
            ageInput.value = "";
            ageInput.disabled = true;
        }
    }

    if (unknownBirth) {
        // 🔥 Чётко ставим чекбокс в выключенное состояние
        unknownBirth.checked = false;

        toggleBirthFields();
        unknownBirth.addEventListener("change", toggleBirthFields);
    }


    // === Редактирование питомца ===
    const editUnknownBirth = document.getElementById("edit-unknown-birth");
    const editBirthInput = document.getElementById("edit-pet-birth");
    const editAgeBlock = document.getElementById("edit-age-block");
    const editAgeInput = document.getElementById("edit-pet-age");

    function toggleEditBirthFields() {
        if (editUnknownBirth.checked) {
            editBirthInput.disabled = true;
            editAgeBlock.style.display = "block";
            editAgeInput.disabled = false;
        } else {
            editBirthInput.disabled = false;
            editAgeBlock.style.display = "none";
            editAgeInput.value = "";
            editAgeInput.disabled = true;
        }
    }

    if (editUnknownBirth) {
        // 🔥 Тоже выключаем по умолчанию
        editUnknownBirth.checked = false;

        toggleEditBirthFields();
        editUnknownBirth.addEventListener("change", toggleEditBirthFields);
    }

});





// =========================
//  СОХРАНЕНИЕ ИЗМЕНЕНИЙ
// =========================
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('edit-pet-modal');
    const closeModal = document.getElementById('close-modal');
    const saveEditBtn = document.getElementById('save-edit-pet');
    const photoInputEdit = document.getElementById('edit-pet-photo');

    if (closeModal) {
        closeModal.addEventListener('click', () => (modal.style.display = 'none'));
    }

    if (saveEditBtn) {
        saveEditBtn.addEventListener('click', async () => {
            const id = document.getElementById('edit-pet-id').value;
            const fd = new FormData();

            fd.append('name', document.getElementById('edit-pet-name').value);
            fd.append('animal_id', document.getElementById('edit-pet-breed').value);

            // —————— Дата рождения или возраст ——————
            const unknownBirth = document.getElementById('edit-unknown-birth').checked;

            if (unknownBirth) {
                fd.append('birth_date', '');
                fd.append('age', document.getElementById('edit-pet-age').value);
            } else {
                fd.append('birth_date', document.getElementById('edit-pet-birth').value);
                fd.append('age', '');
            }

            fd.append('_method', 'PUT');

            // —————— Фото ——————
            if (photoInputEdit.files.length > 0) {
                const file = photoInputEdit.files[0];
                fd.append('photo', file, 'pet.webp');
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

            try {
                const res = await fetch(`/pets/${id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token },
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
                console.error('Ошибка сохранения питомца:', err);
                showToast('Ошибка сети', 'error');
            }
        });
    }

    // —————— Инициализация кроппера при загрузке ——————
    const fileInput = document.getElementById('edit-pet-photo');
    const previewImg = document.getElementById('edit-photo-preview');
    if (fileInput && previewImg) {
        initCropper(fileInput, previewImg);
    }
}

);

document.addEventListener("change", function (e) {
    if (e.target.matches('[id^="pet_photo_input_"]')) {
        const input = e.target;
        const index = input.id.split("_").pop();

        const preview = document.getElementById("photo_preview_" + index);
        const plus    = document.getElementById("plus_icon_" + index);

        const file = input.files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function (event) {
                preview.src = event.target.result;
                preview.style.display = "block";
                plus.style.display = "none"; // Убираем плюсик
            };
            reader.readAsDataURL(file);
        }
    }
});

