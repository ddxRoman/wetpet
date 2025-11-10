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
        const s = species.toLowerCase();
        if (s.includes('кош') || s.includes('cat')) return 'pet-cat';
        if (s.includes('соб') || s.includes('dog')) return 'pet-dog';
        if (s.includes('пти') || s.includes('bird')) return 'pet-bird';
        return 'pet-other';
    };

    function loadPets() {
        fetch('/pets')
            .then(res => res.json())
            .then(data => {
                // селект видов
                typeSelect.innerHTML = '<option value="">Выберите тип животного...</option>';
                const types = [...new Set(data.animals.map(a => a.species))];
                types.forEach(type => {
                    typeSelect.innerHTML += `<option value="${type}">${type}</option>`;
                });

                petsList.innerHTML = '';
                if (data.pets.length === 0) {
                    petsList.innerHTML = '<p>У вас пока нет питомцев.</p>';
                    return;
                }

                data.pets.sort((a, b) => a.name.localeCompare(b.name, 'ru', { sensitivity: 'base' }));

                data.pets.forEach(p => {
                    const cls = getTypeClass(p.animal.species);
                    petsList.innerHTML += `
                        <div class="pet-card ${cls}" data-id="${p.id}">
                            <button class="delete-pet-btn" data-id="${p.id}">✖</button>
                            <img src="${p.photo ? '/storage/' + p.photo : '/storage/pets/default-pet.jpg'}" alt="${p.name}">
                            <b>${p.name}</b><br>
                            <small>${p.animal.species} (${p.animal.breed})</small><br>
                        </div>`;
                });

                document.querySelectorAll('.pet-card').forEach(card => {
                    card.addEventListener('click', () => openEditModal(card.dataset.id));
                });

                document.querySelectorAll('.delete-pet-btn').forEach(btn => {
                    btn.addEventListener('click', e => {
                        e.stopPropagation();
                        if (!confirm('Удалить питомца?')) return;
                        fetch(`/pets/${btn.dataset.id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) {
                                    showToast('Питомец удалён', 'success');
                                    loadPets();
                                } else showToast('Ошибка при удалении', 'error');
                            });
                    });
                });
            });
    }

    addBtn.addEventListener('click', () => {
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });

    saveBtn.addEventListener('click', () => {
        const fd = new FormData(form);
        fetch('/pets', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: fd
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Питомец добавлен!', 'success');
                    form.style.display = 'none';
                    loadPets();
                } else showToast('Ошибка при добавлении', 'error');
            });
    });

    loadPets();
});
