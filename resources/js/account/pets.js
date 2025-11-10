// resources/js/account/pets.js
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

    // preview фото при добавлении
    photoInput.addEventListener('change', e => {
        const file = e.target.files[0];
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    });

    const getTypeClass = species => {
        const s = (species || '').toLowerCase();
        if (s.includes('кош') || s.includes('cat')) return 'pet-cat';
        if (s.includes('соб') || s.includes('dog')) return 'pet-dog';
        if (s.includes('пти') || s.includes('bird')) return 'pet-bird';
        return 'pet-other';
    };

    // Загрузка списка питомцев и рендер карточек
    async function loadPets() {
        try {
            const res = await fetch('/pets', { credentials: 'same-origin' });
            if (!res.ok) throw new Error('Ошибка при загрузке питомцев: ' + res.status);
            const data = await res.json();

            // селект видов
            typeSelect.innerHTML = '<option value="">Выберите тип животного...</option>';
            const types = [...new Set((data.animals || []).map(a => a.species))];
            types.forEach(type => {
                typeSelect.innerHTML += `<option value="${type}">${type}</option>`;
            });

            petsList.innerHTML = '';
            if (!data.pets || data.pets.length === 0) {
                petsList.innerHTML = '<p>У вас пока нет питомцев.</p>';
                return;
            }

            data.pets.sort((a, b) => a.name.localeCompare(b.name, 'ru', { sensitivity: 'base' }));

            data.pets.forEach(p => {
                const cls = getTypeClass(p.animal?.species);
                petsList.insertAdjacentHTML('beforeend', `
                    <div class="pet-card ${cls}" data-id="${p.id}" style="position:relative;">
                        <button class="delete-pet-btn" data-id="${p.id}" aria-label="Удалить питомца"
                            style="position:absolute; top:8px; right:8px; background:#ff4d4f; color:#fff; border:none; border-radius:6px; cursor:pointer; padding:4px 8px;">
                            🗑
                        </button>
                        <img src="${p.photo ? '/storage/' + p.photo : '/storage/pets/default-pet.jpg'}" alt="${p.name}" style="max-width:120px; display:block; margin-bottom:8px;">
                        <b>${p.name}</b><br>
                        <small>${p.animal?.species || ''} (${p.animal?.breed || ''})</small><br>
                    </div>`);
            });

            // Навешиваем обработчики - карточка открывает модалку
            document.querySelectorAll('.pet-card').forEach(card => {
                card.addEventListener('click', () => openEditModal(card.dataset.id));
            });

            // Навешиваем обработчик удаления (делаем это после рендера)
            document.querySelectorAll('.delete-pet-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    e.stopPropagation(); // не открывать модалку при клике
                    const id = btn.getAttribute('data-id');
                    if (!id) {
                        showToast('ID питомца не найден', 'error');
                        return;
                    }
                    if (!confirm('Удалить питомца?')) return;

                    // Лог для дебага
                    console.debug('Delete request for pet id:', id);
                    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

                    // Используем конкатенацию, чтобы избежать проблем с Blade
                    const url = '/pets/' + encodeURIComponent(String(id));
                    console.debug('DELETE URL:', url);

                    try {
                        const delRes = await fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        });

                        // лог статуса для дебага
                        console.debug('DELETE status:', delRes.status);

                        if (delRes.status === 419) {
                            showToast('Сессия истекла — обновите страницу и попробуйте снова', 'error');
                            return;
                        }

                        if (delRes.status === 404) {
                            // 404 — маршрут не найден. Показываем лог.
                            showToast('Маршрут удаления не найден (404). Проверьте routes/web.php', 'error');
                            const text = await delRes.text();
                            console.error('404 response body:', text);
                            return;
                        }

                        const delData = await delRes.json().catch(() => ({}));

                        if (delRes.ok && delData.success) {
                            showToast('Питомец удалён', 'success');

                            // Удаляем карточку из DOM (плавно) — лучше чем сразу reload
                            const card = btn.closest('.pet-card');
                            if (card) card.remove();

                            // Перезагрузим страницу через небольшую задержку, чтобы избежать рассинхрона
                            setTimeout(() => location.reload(), 700);
                        } else {
                            showToast(delData.message || 'Ошибка при удалении', 'error');
                            console.error('Delete response:', delData);
                        }
                    } catch (err) {
                        console.error('Delete error', err);
                        showToast('Ошибка сети', 'error');
                    }
                });
            });

        } catch (err) {
            console.error('loadPets error', err);
            showToast('Не удалось загрузить питомцев', 'error');
        }
    }

    addBtn.addEventListener('click', () => {
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });

    saveBtn.addEventListener('click', async (e) => {
        e.preventDefault();
        const fd = new FormData(form);
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res = await fetch('/pets', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token },
                credentials: 'same-origin',
                body: fd
            });
            const data = await res.json();
            if (res.ok && data.success) {
                showToast('Питомец добавлен!', 'success');
                form.style.display = 'none';
                await loadPets();
            } else {
                showToast(data.message || 'Ошибка при добавлении', 'error');
                console.error('Add pet response:', data);
            }
        } catch (err) {
            console.error('save pet error', err);
            showToast('Ошибка сети при добавлении питомца', 'error');
        }
    });

    loadPets();
});
