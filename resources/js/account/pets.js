import { showToast } from './toast';
import { openEditModal } from './pets-edit';

document.addEventListener('DOMContentLoaded', () => {
    const addBtn = document.getElementById('add-pet-btn');
    const form = document.getElementById('add-pet-form');
    const saveBtn = document.getElementById('save-pet-btn');
    const typeSelect = document.getElementById('type-select');
    const breedSelect = document.getElementById('breed-select');
    const petsList = document.getElementById('pets-list');
    const birthInput = document.getElementById('pet-birth');
    const ageInput = document.getElementById('pet-age');
    const unknownBirth = document.getElementById('unknown-birth');
    const birthBlock = document.getElementById('birth-block');
    const ageBlock = document.getElementById('age-block');
    const photoInput = document.getElementById('pet-photo');
    const preview = document.getElementById('photo-preview');

    if (!addBtn) return;

    // Превью фото
    photoInput.addEventListener('change', e => {
        const file = e.target.files[0];
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    });

    // Класс карточки по типу
    const getTypeClass = species => {
        const s = (species || '').toLowerCase();
        if (s.includes('кош') || s.includes('cat')) return 'pet-cat';
        if (s.includes('соб') || s.includes('dog')) return 'pet-dog';
        if (s.includes('пти') || s.includes('bird')) return 'pet-bird';
        return 'pet-other';
    };

    // === Загрузка питомцев ===
    async function loadPets() {
        try {
            const res = await fetch('/pets', { credentials: 'same-origin' });
            if (!res.ok) throw new Error('Ошибка при загрузке питомцев: ' + res.status);
            const data = await res.json();

            // Типы животных
            typeSelect.innerHTML = '<option value="">Выберите тип животного...</option>';
            const types = [...new Set((data.animals || []).map(a => a.species))];
            types.forEach(type => {
                typeSelect.innerHTML += `<option value="${type}">${type}</option>`;
            });

            // Список питомцев
            petsList.innerHTML = '';
            if (!data.pets || data.pets.length === 0) {
                petsList.innerHTML = '<p>У вас пока нет питомцев.</p>';
                return;
            }

            data.pets.sort((a, b) => a.name.localeCompare(b.name, 'ru', { sensitivity: 'base' }));

            data.pets.forEach(p => {
                const cls = getTypeClass(p.animal?.species);
petsList.insertAdjacentHTML('beforeend', `
    <div class="pet-card ${cls}"
         data-id="${p.id}"
         data-name="${p.name}"
         data-gender="${p.gender || ''}"
         data-birth="${p.birth_date || ''}"
         data-age="${p.age || ''}"
         data-breed="${p.animal?.breed || ''}"
         data-breed-id="${p.animal_id || ''}"
         data-photo="${p.photo ? '/storage/' + p.photo : '/storage/pets/default-pet.jpg'}"
         style="position:relative;">

        <button class="delete-pet-btn" data-id="${p.id}" aria-label="Удалить питомца"
            style="position:absolute; top:8px; right:8px; background:#ff4d4f; color:#fff; border:none; border-radius:6px; cursor:pointer; padding:4px 8px;">
            🗑
        </button>

        <img src="${p.photo ? '/storage/' + p.photo : '/storage/pets/default-pet.jpg'}"
             alt="${p.name}"
             title="фотография животного"
             style="max-width:100%; display:block; margin-bottom:8px; border-radius:10px;">

        <b>${p.name}</b><br>
        <small>${p.animal?.species || ''} (${p.animal?.breed || ''})</small><br>
    </div>
`);

            });

            // Открытие модалки
            document.querySelectorAll('.pet-card').forEach(card => {
                card.addEventListener('click', () => openEditModal(card.dataset.id));
            });

            // Удаление питомца
            document.querySelectorAll('.delete-pet-btn').forEach(btn => {
                btn.addEventListener('click', async e => {
                    e.stopPropagation();
                    const id = btn.getAttribute('data-id');
                    if (!id) return showToast('ID питомца не найден', 'error');
                    if (!confirm('Удалить питомца?')) return;

                    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const url = '/pets/' + encodeURIComponent(String(id));

                    try {
                        const delRes = await fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        });

                        const delData = await delRes.json().catch(() => ({}));
                        if (delRes.ok && delData.success) {
                            showToast('Питомец удалён', 'success');
                            btn.closest('.pet-card')?.remove();
                            await loadPets();
                        } else {
                            showToast(delData.message || 'Ошибка при удалении', 'error');
                        }
                    } catch (err) {
                        console.error('Ошибка удаления питомца:', err);
                        showToast('Ошибка сети', 'error');
                    }
                });
            });

        } catch (err) {
            console.error('Ошибка загрузки питомцев:', err);
            showToast('Не удалось загрузить питомцев', 'error');
        }
    }

    // === Подгрузка пород ===
    typeSelect.addEventListener('change', async () => {
        const type = typeSelect.value;
        breedSelect.innerHTML = '<option value="">Загрузка пород...</option>';
        breedSelect.disabled = true;

        if (!type) {
            breedSelect.innerHTML = '<option value="">Сначала выберите тип животного</option>';
            return;
        }

        try {
            const res = await fetch(`/breeds?type=${encodeURIComponent(type)}`, {
                credentials: 'same-origin'
            });
            if (!res.ok) throw new Error('Ошибка при загрузке пород');
            const data = await res.json();

            breedSelect.innerHTML = '<option value="">Выберите породу...</option>';

            if (!Array.isArray(data) || data.length === 0) {
                breedSelect.innerHTML = '<option value="">Нет пород для этого типа</option>';
            } else {
                data.forEach(b => {
                    breedSelect.innerHTML += `<option value="${b.id}">${b.name}</option>`;
                });
            }
        } catch (err) {
            console.error('Ошибка при загрузке пород:', err);
            breedSelect.innerHTML = '<option value="">Ошибка загрузки</option>';
            showToast('Не удалось загрузить породы', 'error');
        } finally {
            breedSelect.disabled = false;
        }
    });

    // === Добавление питомца ===
    addBtn.addEventListener('click', () => {
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });

    saveBtn.addEventListener('click', async e => {
        e.preventDefault();

        const breedId = breedSelect.value;
        if (!breedId) return showToast('Выберите породу', 'error');

        const formData = new FormData();
        formData.append('animal_id', breedId);
        formData.append('name', document.getElementById('pet-name').value || '');
        formData.append('gender', document.getElementById('pet-gender').value || '');
        formData.append('birth_date', document.getElementById('pet-birth').value || '');
        formData.append('age', document.getElementById('pet-age').value || '');

        const file = document.getElementById('pet-photo').files[0];
        if (file) formData.append('photo', file);

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res = await fetch('/pets', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token },
                credentials: 'same-origin',
                body: formData
            });

            const data = await res.json();
            if (res.ok && data.success) {
                showToast('Питомец добавлен!', 'success');
                form.style.display = 'none';
                await loadPets();
            } else {
                console.error('Ошибка добавления питомца:', data);
                showToast(data.message || 'Ошибка при добавлении', 'error');
            }
        } catch (err) {
            console.error('Ошибка сети при добавлении питомца', err);
            showToast('Ошибка сети при добавлении питомца', 'error');
        }
    });

    // Первичная загрузка
    loadPets();
});
