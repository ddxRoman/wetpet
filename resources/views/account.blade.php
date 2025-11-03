@extends('layouts.app')
@vite(['resources/css/main.css','resources/sass/app.scss', 'resources/js/app.js'])

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
                        <input type="date" name="birth_date" value="{{ $user->birth_date ?? '' }}">
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
                // типы
                typeSelect.innerHTML = '<option value="">Выберите тип животного...</option>';
                const types = [...new Set(data.animals.map(a => a.species))];
                types.forEach(type => typeSelect.innerHTML += `<option value="${type}">${type}</option>`);

                // карточки
                petsList.innerHTML = '';
                data.pets.forEach(p => {
                    const cls = getTypeClass(p.animal.species);
                    petsList.innerHTML += `
                        <div class="pet-card ${cls}">
                            <img src="${p.photo ? '/storage/' + p.photo : '/storage/pets/default-pet.jpg'}" alt="${p.name}">
                            <b>${p.name}</b>
                            <div>${p.animal.species} (${p.animal.breed})</div>
                            <div>${p.birth_date ? 'Дата рождения: ' + p.birth_date : 'Возраст: ' + (p.age ?? '-') + ' лет'}</div>
                        </div>
                    `;
                });
            });
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
            alert('Питомец добавлен!');
            form.style.display = 'none';
            loadPets();
        } else {
            alert('Ошибка при сохранении питомца');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Ошибка сети при добавлении питомца.');
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

</body>
</html>
@endsection
