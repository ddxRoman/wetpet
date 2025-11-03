@extends('layouts.app')
@vite(['resources/css/main.css','resources/sass/app.scss', 'resources/js/app.js'])

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>


@section('content')
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
</head>
<style>
    .py-4 { padding: 0 !important; }
    #avatar-input { display: none; }
</style>

<body class="body_page">
    {{-- 🔹 Навбар --}}
    <nav class="navbar">
        <div class="navbar-logo">
            <a href="{{ url('/') }}">
                <img src="{{ Storage::url('logo/logo3.png') }}" alt="Логотип">
            </a>
        </div>
        <div class="navbar-user">
            {{ Auth::user()->nickname ?? Auth::user()->name }}
        </div>
    </nav>

    <div class="account-container">
        <div class="sidebar">
            <button class="tab-btn active" data-tab="profile">Профиль</button>
            <button class="tab-btn" data-tab="pets">Питомцы</button>
            <!-- <button class="tab-btn" data-tab="favorits">Избранное</button> -->
            <!-- <button class="tab-btn" data-tab="rewievs">Отзывы</button> -->
        </div>

        <div class="account-content">
            {{-- 🔹 Вкладка профиля --}}
            <div class="tab-content" id="profile">
                <h2 title="{{ $user->name }}">Профиль {{ $user->nickname ?? '' }}</h2>
                <p>С нами с {{ $user->created_at->format('d.m.Y') }}</p>

                <form method="POST" action="{{ route('account.updateProfile') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group avatar-upload">
                        <label for="avatar-input">
                            <img title="Изменить фото"
                                 src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('storage/default-avatar.png') }}"
                                 alt="Аватар"
                                 class="avatar-preview"
                                 id="avatar-preview"
                                 style="cursor:pointer;">
                        </label>
                        <input type="file" id="avatar-input" name="avatar" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>ФИО *</label>
                        <input type="text" name="name" value="{{ $user->name }}" required>
                    </div>

                    <div class="form-group">
                        <label>Ник *</label>
                        <input type="text" name="nickname" value="{{ $user->nickname ?? '' }}" required>
                    </div>

                    <div class="form-group">
                        <label>Дата рождения</label>
                        <input type="date" name="birth_date" value="{{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->format('Y-m-d') : '' }}">

                    </div>

                    <div class="form-group">
                        <label>Город</label>
                        <select id="city-select" name="city_slug" style="width:100%;"></select>

                        <div id="new-city-fields" style="display:none; margin-top:15px;">
                            <input type="text" id="new-country" placeholder="Страна" style="margin-bottom:8px;">
                            <input type="text" id="new-region" placeholder="Область / Край" style="margin-bottom:8px;">
                            <input type="text" id="new-name" placeholder="Название города">
                            <button type="button" id="save-city-btn"
                                style="margin-top:10px; background:#007bff; color:#fff; border:none; padding:8px 14px; border-radius:6px;">
                                Сохранить город
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Почта *</label>
                        <input type="email" name="email" value="{{ $user->email }}" required>
                    </div>

                    <button type="submit" class="save-btn">Сохранить изменения</button>
                </form>
            </div>

            {{-- 🔹 Вкладка питомцев --}}
            <div class="tab-content" id="pets" style="display:none;">
                <h2>Мои питомцы</h2>
                <button id="add-pet-btn" class="save-btn" style="margin-bottom:15px;">+ Добавить питомца</button>

<div id="add-pet-form" style="display:none; margin-bottom:20px;">
    <select id="type-select" style="width:100%; margin-bottom:10px;">
        <option value="">Выберите тип животного...</option>
    </select>

    <select id="breed-select" style="width:100%; margin-bottom:10px;" disabled>
        <option value="">Сначала выберите тип...</option>
    </select>

    <input type="text" id="pet-name" placeholder="Имя питомца" style="width:100%; margin-bottom:10px;">

    <label>Пол питомца:</label>
<select id="pet-gender" style="width:100%; margin-bottom:10px;">
    <option value="">Выберите пол...</option>
    <option value="male">Самец</option>
    <option value="female">Самка</option>
</select>


    <div id="birth-block">
        <label>Дата рождения:</label>
        <input type="date" id="pet-birth" style="width:100%;">
    </div>

    <label style="display:flex; align-items:center; gap:8px; margin-top:8px;">
        <input type="checkbox" id="unknown-birth"> Я не знаю точной даты
    </label>

    <div id="age-block" style="display:none;">
        <label>Возраст:</label>
        <input type="number" id="pet-age" min="0" style="width:100%;">
    </div>

    <!-- 🔹 Новое поле: Фото питомца -->
    <label style="display:block; margin-top:10px;">Фото питомца:</label>
    <input type="file" id="pet-photo" accept="image/*" style="width:100%; margin-bottom:10px;">
    <img id="photo-preview" src="" alt="" style="max-width:100px; display:none; border-radius:8px; margin-bottom:10px;">

    <button id="save-pet-btn" class="save-btn" style="margin-top:10px;">Сохранить</button>
</div>

<div id="pets-list" class="pets-grid"></div>


                <div id="pets-list"></div>
            </div>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- 🔹 Предпросмотр аватара --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('avatar-input');
    const previewImg = document.getElementById('avatar-preview');
    fileInput.addEventListener('change', function() {
        if (this.files[0]) previewImg.src = URL.createObjectURL(this.files[0]);
    });
});
</script>

{{-- 🔹 Города --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const citySelect = $('#city-select');
    const newCityFields = document.getElementById('new-city-fields');
    const saveCityBtn = document.getElementById('save-city-btn');
    const userCitySlug = '{{ $user->city->slug ?? '' }}';

    fetch('{{ route('cities.all') }}')
        .then(res => res.json())
        .then(cities => {
            cities.forEach(city => {
                citySelect.append(new Option(city.name, city.slug));
            });
            citySelect.append(new Option('+ Моего города нет в списке', 'add_new_city'));
            citySelect.select2({ placeholder: "Введите город...", width: '100%' });
            if (userCitySlug) citySelect.val(userCitySlug).trigger('change');
        });

    citySelect.on('change', function() {
        const value = $(this).val();
        if (value === 'add_new_city') {
            newCityFields.style.display = 'block';
        } else {
            newCityFields.style.display = 'none';
            if (value) {
                fetch('{{ route('account.updateCity') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ city_slug: value })
                });
            }
        }
    });

    saveCityBtn.addEventListener('click', () => {
        const name = document.getElementById('new-name').value.trim();
        const country = document.getElementById('new-country').value.trim();
        const region = document.getElementById('new-region').value.trim();
        if (!name || !country || !region) return alert('Заполните все поля.');

        fetch('{{ route('cities.add') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ name, country, region })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const newOption = new Option(data.city.name, data.city.slug, true, true);
                citySelect.append(newOption).trigger('change');
                newCityFields.style.display = 'none';
                alert('Город добавлен!');
            } else alert('Ошибка при добавлении города');
        });
    });
});
</script>

{{-- 🔹 Питомцы (новая версия с 2 селектами) --}}
<script>
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

    // Предпросмотр фото
    photoInput.addEventListener('change', e => {
        const file = e.target.files[0];
        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    });

    function getTypeClass(species) {
        const s = species.toLowerCase();
        if (s.includes('кош') || s.includes('cat')) return 'pet-cat';
        if (s.includes('соб') || s.includes('dog')) return 'pet-dog';
        if (s.includes('пти') || s.includes('bird')) return 'pet-bird';
        return 'pet-other';
    }

function loadPets() {
    fetch('{{ route('pets.index') }}')
        .then(res => res.json())
        .then(data => {
            // 🔹 Заполняем селект типов животных
            typeSelect.innerHTML = '<option value="">Выберите тип животного...</option>';
            const types = [...new Set(data.animals.map(a => a.species))];
            types.forEach(type => {
                typeSelect.innerHTML += `<option value="${type}">${type}</option>`;
            });

            // 🔹 Выводим карточки питомцев
            petsList.innerHTML = '';
            if (data.pets.length === 0) {
                petsList.innerHTML = '<p>У вас пока нет питомцев.</p>';
                return;
            }

            data.pets.sort((a, b) => a.name.localeCompare(b.name, 'ru', { sensitivity: 'base' }));

data.pets.forEach(p => {
  const cls = getTypeClass(p.animal.species);
  petsList.innerHTML += `
    <div class="pet-card ${cls}" data-id="${p.id}" style="cursor:pointer; border:1px solid #ddd; border-radius:10px; padding:10px; margin-bottom:10px; position:relative;">
        <button class="delete-pet-btn" data-id="${p.id}" 
            style="position:absolute; top:8px; right:8px; background:#ff4d4f; color:#fff; border:none; border-radius:6px; cursor:pointer; padding:4px 8px;">
            ✖
        </button>
        <img src="${p.photo ? '/storage/' + p.photo : '/storage/pets/default-pet.jpg'}"
             alt="${p.name}"
             style="width:100%; max-width:120px; border-radius:10px; display:block; margin-bottom:8px;">
        <b>${p.name}</b><br>
        <small>${p.animal.species} (${p.animal.breed})</small><br>
        ${p.gender ? `<small>Пол: ${p.gender === 'male' ? 'самец' : 'самка'}</small><br>` : ''}
        ${p.birth_date ? `<small>Дата рождения: ${p.birth_date}</small>` : (p.age ? `<small>Возраст: ${p.age} лет</small>` : '')}
    </div>
  `;
});


            // 🔹 Навешиваем обработчики клика для редактирования
            document.querySelectorAll('.pet-card').forEach(card => {
                card.addEventListener('click', () => openEditModal(card.dataset.id));
            });

            // 🔹 Удаление питомца
document.querySelectorAll('.delete-pet-btn').forEach(btn => {
    btn.addEventListener('click', e => {
        e.stopPropagation(); // не открывать модалку при клике
        const id = btn.dataset.id;
        if (!confirm('Удалить питомца?')) return;

        fetch(`/pets/${id}`, {
            method: 'DELETE',
            headers: { 
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('Питомец удалён', 'success');
                loadPets();
            } else {
                showToast('Ошибка при удалении', 'error');
            }
        })
        .catch(() => showToast('Ошибка сети', 'error'));
    });
});


        })
        .catch(err => console.error('Ошибка загрузки питомцев:', err));
}







    typeSelect.addEventListener('change', () => {
        const selectedType = typeSelect.value;
        breedSelect.innerHTML = '<option>Загрузка...</option>';
        breedSelect.disabled = true;
        fetch('{{ route('pets.index') }}')
            .then(res => res.json())
            .then(data => {
                const breeds = data.animals.filter(a => a.species === selectedType);
                breedSelect.innerHTML = '<option value="">Выберите породу...</option>';
                breeds.forEach(b => breedSelect.innerHTML += `<option value="${b.id}">${b.breed}</option>`);
                breedSelect.disabled = false;
            });
    });

    addBtn.addEventListener('click', () => {
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });

    unknownBirth.addEventListener('change', () => {
        if (unknownBirth.checked) {
            birthBlock.style.display = 'none';
            ageBlock.style.display = 'block';
        } else {
            birthBlock.style.display = 'block';
            ageBlock.style.display = 'none';
        }
    });

 saveBtn.addEventListener('click', () => {
    const animal_id = breedSelect.value;
    const name = document.getElementById('pet-name').value.trim();
    const birth_date = birthInput.value || null;
    const age = unknownBirth.checked ? ageInput.value : null;

    if (!animal_id || !name) {
        alert('Заполните все обязательные поля!');
        return;
    }

    const fd = new FormData();
    fd.append('animal_id', animal_id);
    fd.append('name', name);
    if (birth_date) fd.append('birth_date', birth_date);
    if (age) fd.append('age', age);
const gender = document.getElementById('pet-gender').value;
if (gender) fd.append('gender', gender);

    const photoInput = document.getElementById('pet-photo');
    if (photoInput && photoInput.files[0]) {
        fd.append('photo', photoInput.files[0]);
    }

    fetch('{{ route("pets.store") }}', {
        method: 'POST',
        credentials: 'same-origin', // ✅ Laravel теперь видит сессию
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: fd
    })
    .then(res => {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    })
    .then(data => {
        if (data.success) {
showToast('Питомец добавлен!', 'success');

            form.style.display = 'none';
            loadPets();
        } else {
showToast('Ошибка при добавлении питомца', 'error');

        }
    })
    .catch(err => {
        console.error(err);
showToast('Ошибка сети при добавлении питомца', 'error');

    });
});

// === Модалка редактирования ===
// === Модалка редактирования ===
const modal = document.getElementById('edit-pet-modal');
const closeModal = document.getElementById('close-modal');
const saveEditBtn = document.getElementById('save-edit-pet');
const previewEdit = document.getElementById('edit-photo-preview');
const unknownEdit = document.getElementById('edit-unknown-birth');
const birthBlockEdit = document.getElementById('edit-birth-block');
const ageBlockEdit = document.getElementById('edit-age-block');
const breedSelectEdit = document.getElementById('edit-pet-breed');

closeModal.addEventListener('click', () => modal.style.display = 'none');

// переключатель "не знаю дату"
unknownEdit.addEventListener('change', () => {
  if (unknownEdit.checked) {
    birthBlockEdit.style.display = 'none';
    ageBlockEdit.style.display = 'block';
  } else {
    birthBlockEdit.style.display = 'block';
    ageBlockEdit.style.display = 'none';
  }
});

function openEditModal(petId) {
  fetch(`/pets/${petId}`, {
    method: 'GET',
    credentials: 'same-origin',
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    }
  })
  .then(res => {
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  })
  .then(p => {
    document.getElementById('edit-pet-id').value = p.id;
    document.getElementById('edit-pet-name').value = p.name;
    document.getElementById('edit-pet-birth').value = p.birth_date ?? '';
    document.getElementById('edit-pet-age').value = p.age ?? '';

    // переключение полей
    if (p.birth_date) {
      unknownEdit.checked = false;
      birthBlockEdit.style.display = 'block';
      ageBlockEdit.style.display = 'none';
    } else {
      unknownEdit.checked = true;
      birthBlockEdit.style.display = 'none';
      ageBlockEdit.style.display = 'block';
    }

    // Породы
fetch('{{ route("pets.index") }}')
  .then(r => r.json())
  .then(data => {
    // находим тип (вид) текущего питомца
    const currentType = p.animal.species;
    // фильтруем породы только по этому типу
    const breeds = data.animals.filter(a => a.species === currentType);

    breedSelectEdit.innerHTML = '';
    breeds.forEach(a => {
      breedSelectEdit.innerHTML += `<option value="${a.id}" ${a.id === p.animal_id ? 'selected' : ''}>${a.breed}</option>`;
    });
  });


if (p.photo) {
  previewEdit.src = '/storage/' + p.photo;
} else {
  previewEdit.src = '/storage/pets/default-pet.jpg';
}
previewEdit.style.display = 'block';


    modal.style.display = 'flex';
  })
  .catch(err => {
    console.error('Ошибка загрузки питомца:', err);
    alert('Не удалось загрузить данные питомца');
  });
}

// Предпросмотр нового фото
document.getElementById('edit-pet-photo').addEventListener('change', e => {
  const f = e.target.files[0];
  const preview = document.getElementById('edit-photo-preview');
  if (f) {
    preview.src = URL.createObjectURL(f);
    preview.style.display = 'block';
  }
});


// === Сохранение изменений ===
saveEditBtn.addEventListener('click', () => {
  const id = document.getElementById('edit-pet-id').value;
  const name = document.getElementById('edit-pet-name').value.trim();
  const animal_id = document.getElementById('edit-pet-breed').value;
  const birth = unknownEdit.checked ? '' : document.getElementById('edit-pet-birth').value;
  const age = unknownEdit.checked ? document.getElementById('edit-pet-age').value : '';
  const file = document.getElementById('edit-pet-photo').files[0];

  if (!name || !animal_id) return alert('Заполните обязательные поля.');

  const fd = new FormData();
  fd.append('name', name);
  fd.append('animal_id', animal_id);
  if (birth) fd.append('birth_date', birth);
  if (age) fd.append('age', age);
  if (file) fd.append('photo', file);
  fd.append('_method', 'PUT');

  fetch(`/pets/${id}`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
    body: fd
  })
  .then(r => r.json().catch(() => { throw new Error('JSON parse error'); }))
  .then(data => {
    if (data.success) {
showToast('Сохранено', 'success');

      modal.style.display = 'none';
      loadPets();
    } else {
      console.error(data);
      showToast('Ошибка', 'error');

    }
  })
  .catch(e => {
    console.error(e);
    showToast('Ошибка сети', 'error');

  });
});



    loadPets();
});
</script>


{{-- 🔹 Переключение вкладок --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.dataset.tab;
            tabButtons.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.style.display = 'none');
            this.classList.add('active');
            document.getElementById(tab).style.display = 'block';
        });
    });
});

fetch('/test-pets', {
  method: 'POST',
  headers: { 
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': '{{ csrf_token() }}'
  },
  body: JSON.stringify({ test: 'ok' })
})
.then(r => r.json())
.then(console.log)


</script>


<!-- 🔹 Модалка редактирования питомца -->
<div id="edit-pet-modal" class="modal" style="
    display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5);
    justify-content:center; align-items:center; z-index:9999;">
  <div style="background:#fff; padding:20px; border-radius:10px; width:90%; max-width:400px; position:relative;">
    <button id="close-modal" style="position:absolute; top:8px; right:10px; background:none; border:none; font-size:18px;">✖</button>
    <h3>Редактировать питомца</h3>

    <input type="hidden" id="edit-pet-id">

    <div style="margin-bottom:10px;">
      <label>Имя *</label>
      <input type="text" id="edit-pet-name" style="width:100%;">
    </div>

    <div style="margin-bottom:10px;">
      <label>Порода *</label>
      <select id="edit-pet-breed" style="width:100%;"></select>
    </div>

    <div id="edit-birth-block" style="margin-bottom:10px;">
      <label>Дата рождения</label>
      <input type="date" id="edit-pet-birth" style="width:100%;">
    </div>

    <label style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
      <input type="checkbox" id="edit-unknown-birth"> Я не знаю точную дату
    </label>

    <div id="edit-age-block" style="display:none; margin-bottom:10px;">
      <label>Возраст</label>
      <input type="number" id="edit-pet-age" style="width:100%;" min="0">
    </div>

<div style="margin-bottom:10px; text-align:center;">
  <label for="edit-pet-photo" style="cursor:pointer; display:inline-block;">
    <img id="edit-photo-preview"
         src="/storage/pets/default-pet.jpg"
         alt="Фото питомца"
         style="max-width:150px; border-radius:10px; margin-bottom:8px; border:2px solid #ddd; transition:0.3s;">
  </label>
  <input type="file" id="edit-pet-photo" accept="image/*" style="display:none;">
  <p style="font-size:13px; color:#666;">Нажмите на фото, чтобы добавить или изменить</p>
</div>



    <button id="save-edit-pet" class="save-btn">Сохранить изменения</button>
  </div>
</div>


<!-- 🔹 Контейнер для уведомлений -->
<div id="toast-container" style="position:fixed; top:20px; right:20px; z-index:10000;"></div>

<style>
.toast {
    background: #fff;
    color: #333;
    border-left: 5px solid #007bff;
    padding: 12px 16px;
    margin-top: 10px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    font-family: system-ui, sans-serif;
    min-width: 250px;
    animation: fadeInOut 4s ease forwards;
}
.toast.success { border-color: #28a745; }
.toast.error { border-color: #dc3545; }
.toast.warning { border-color: #ffc107; }

@keyframes fadeInOut {
    0% { opacity: 0; transform: translateY(-10px); }
    10%, 90% { opacity: 1; transform: translateY(0); }
    100% { opacity: 0; transform: translateY(-10px); }
}
</style>

<script>
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}
</script>



<!-- 🔹 Универсальная модалка кадрирования -->
<div id="cropper-modal" style="
    display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6);
    justify-content:center; align-items:center; z-index:10000;">
  <div style="background:#fff; padding:20px; border-radius:10px; max-width:90vw; max-height:90vh;">
    <img id="cropper-image" src="" alt="crop" style="max-width:100%; max-height:70vh;">
    <div style="text-align:center; margin-top:10px;">
      <button id="cropper-save" class="save-btn">Обрезать</button>
      <button id="cropper-cancel" class="save-btn" style="background:#aaa;">Отмена</button>
    </div>
  </div>
</div>

<script>
let cropperInstance = null;
let currentInput = null;
let currentPreview = null;

function initCropper(inputId, previewId, aspectRatio = 1) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    const modal = document.getElementById('cropper-modal');
    const cropImage = document.getElementById('cropper-image');
    const saveBtn = document.getElementById('cropper-save');
    const cancelBtn = document.getElementById('cropper-cancel');

    input.addEventListener('change', e => {
        const file = e.target.files[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        cropImage.src = url;
        currentInput = input;
        currentPreview = preview;

        modal.style.display = 'flex';
        setTimeout(() => {
            cropperInstance = new Cropper(cropImage, {
                aspectRatio,
                viewMode: 1,
                autoCropArea: 1
            });
        }, 100);
    });

    cancelBtn.onclick = () => {
        modal.style.display = 'none';
        cropperInstance?.destroy();
    };

    saveBtn.onclick = () => {
        const canvas = cropperInstance.getCroppedCanvas({
            width: 600,
            height: 600
        });

        // Показываем preview и заменяем input-файл
        canvas.toBlob(blob => {
            const webpFile = new File([blob], 'photo.webp', { type: 'image/webp' });
            const dt = new DataTransfer();
            dt.items.add(webpFile);
            currentInput.files = dt.files;

            currentPreview.src = URL.createObjectURL(webpFile);
        }, 'image/webp', 0.9);

        modal.style.display = 'none';
        cropperInstance.destroy();
    };
}
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 🧩 Аватар пользователя (квадрат)
    initCropper('avatar-input', 'avatar-preview', 1);

    // 🧩 Фото питомца при добавлении
    initCropper('pet-photo', 'photo-preview', 1);

    // 🧩 Фото питомца при редактировании
    initCropper('edit-pet-photo', 'edit-photo-preview', 1);
});
</script>



</body>
</html>
@endsection
