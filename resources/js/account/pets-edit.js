import { showToast } from './toast';
import { initCropper } from './cropper-init'; // ✅ правильный путь

export function openEditModal(petId) {
    const modal = document.getElementById('edit-pet-modal');
    const previewEdit = document.getElementById('edit-photo-preview');
    const photoInputEdit = document.getElementById('edit-pet-photo');
    const breedSelectEdit = document.getElementById('edit-pet-breed');

    // 🔹 Находим карточку питомца
    const card = document.querySelector(`.pet-card[data-id="${petId}"]`);
    if (!card) {
        showToast('Питомец не найден на странице', 'error');
        return;
    }

    // 🔹 Извлекаем данные из карточки
    const name = card.querySelector('b')?.textContent?.trim() || '';
    const info = card.querySelector('small')?.textContent?.trim() || '';
    const photo = card.querySelector('img')?.getAttribute('src') || '';

    // 🔹 Разбираем "Тип (Порода)"
    let species = '', breed = '';
    if (info.includes('(')) {
        const [typePart, breedPart] = info.split('(');
        species = typePart.trim();
        breed = breedPart.replace(')', '').trim();
    } else {
        species = info.trim();
    }

    // 🔹 Заполняем базовые поля модалки
    document.getElementById('edit-pet-id').value = petId;
    document.getElementById('edit-pet-name').value = name;
    document.getElementById('edit-pet-birth').value = '';
    document.getElementById('edit-pet-age').value = '';

    // 🔹 Фото — показываем текущее
    previewEdit.src = photo || '/storage/pets/default-pet.jpg';
    previewEdit.style.display = 'block';

    // 🔹 Подключаем кроппер
    photoInputEdit.value = ''; // сбрасываем старое фото
    initCropper(photoInputEdit, previewEdit);

    // 🔹 Загружаем список пород
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

// 💾 Сохранение изменений
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('edit-pet-modal');
    const closeModal = document.getElementById('close-modal');
    const saveEditBtn = document.getElementById('save-edit-pet');
    const photoInputEdit = document.getElementById('edit-pet-photo');
    const previewEdit = document.getElementById('edit-photo-preview');

    if (closeModal) {
        closeModal.addEventListener('click', () => (modal.style.display = 'none'));
    }

    if (saveEditBtn) {
        saveEditBtn.addEventListener('click', async () => {
            const id = document.getElementById('edit-pet-id').value;
            const fd = new FormData();
            fd.append('name', document.getElementById('edit-pet-name').value);
            fd.append('animal_id', document.getElementById('edit-pet-breed').value);
            fd.append('birth_date', document.getElementById('edit-pet-birth').value);
            fd.append('age', document.getElementById('edit-pet-age').value);
            fd.append('_method', 'PUT');

            // 🔹 Если фото выбрано
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

    // Инициализация кроппера (чтобы сработало при загрузке)
    const fileInput = document.getElementById('edit-pet-photo');
    const previewImg = document.getElementById('edit-photo-preview');
    if (fileInput && previewImg) {
        initCropper(fileInput, previewImg);
    }
});
