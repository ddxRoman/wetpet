import { showToast } from './toast';

export function openEditModal(petId) {
    const modal = document.getElementById('edit-pet-modal');
    const previewEdit = document.getElementById('edit-photo-preview');
    const breedSelectEdit = document.getElementById('edit-pet-breed');

    fetch(`/pets/${petId}`)
        .then(r => r.json())
        .then(p => {
            document.getElementById('edit-pet-id').value = p.id;
            document.getElementById('edit-pet-name').value = p.name;
            document.getElementById('edit-pet-birth').value = p.birth_date ?? '';
            document.getElementById('edit-pet-age').value = p.age ?? '';

            fetch('/pets')
                .then(r => r.json())
                .then(data => {
                    const breeds = data.animals.filter(a => a.species === p.animal.species);
                    breedSelectEdit.innerHTML = '';
                    breeds.forEach(a => {
                        breedSelectEdit.innerHTML += `<option value="${a.id}" ${a.id === p.animal_id ? 'selected' : ''}>${a.breed}</option>`;
                    });
                });

            previewEdit.src = p.photo ? '/storage/' + p.photo : '/storage/pets/default-pet.jpg';
            previewEdit.style.display = 'block';
            modal.style.display = 'flex';
        });
}

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('edit-pet-modal');
    const closeModal = document.getElementById('close-modal');
    const saveEditBtn = document.getElementById('save-edit-pet');

    closeModal.addEventListener('click', () => modal.style.display = 'none');

    saveEditBtn.addEventListener('click', () => {
        const id = document.getElementById('edit-pet-id').value;
        const fd = new FormData();
        fd.append('name', document.getElementById('edit-pet-name').value);
        fd.append('animal_id', document.getElementById('edit-pet-breed').value);
        fd.append('birth_date', document.getElementById('edit-pet-birth').value);
        fd.append('_method', 'PUT');

        fetch(`/pets/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: fd
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Сохранено', 'success');
                    modal.style.display = 'none';
                } else showToast('Ошибка сохранения', 'error');
            });
    });
});
