@extends('layouts.app')
@vite(['resources/css/main.css','resources/sass/app.scss', 'resources/js/app.js'])

@section('content')
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
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
            <button class="tab-btn" data-tab="favorits">Избранное</button>
            <button class="tab-btn" data-tab="rewievs">Отзывы</button>
        </div>

        <div class="account-content">
            <div class="tab-content" id="profile">
                <h2 title="{{ $user->name }}">Профиль {{ $user->nickname ?? '' }}</h2>
                <p>С нами с {{ $user->created_at->format('d.m.Y') }}</p>

                <form method="POST" action="{{ route('account.updateProfile') }}" enctype="multipart/form-data">
                    @csrf

                    {{-- 🔹 Аватар (кликабельный для загрузки) --}}
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

                    {{-- 🔹 Селект городов --}}
                    <div class="form-group">
                        <label>Город</label>
                        <div style="position: relative;">
                            <select id="city-select" name="city_id" class="city-select" style="width: 100%;">
                                <option value="">Выберите город...</option>
                            </select>
                        </div>

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
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('avatar-input');
    const previewImg = document.getElementById('avatar-preview');

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            previewImg.src = URL.createObjectURL(file);
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const citySelect = $('#city-select');
    const newCityFields = document.getElementById('new-city-fields');
    const saveCityBtn = document.getElementById('save-city-btn');
    const userCityId = '{{ $user->city_id ?? '' }}';

    // 🔹 Загружаем города
    fetch('{{ route('cities.all') }}')
        .then(res => res.json())
        .then(cities => {
            // Добавляем города
            cities.forEach(city => {
                const option = new Option(city.name, city.id, false, false);
                citySelect.append(option);
            });

            // Добавляем пункт "+ Моего города нет в списке"
            const addNewOption = new Option('+ Моего города нет в списке', 'add_new_city', false, false);
            citySelect.append(addNewOption);

            // Инициализация select2
            citySelect.select2({
                placeholder: "Введите название города...",
                allowClear: true,
                width: '100%',
                language: {
                    searching: () => "Поиск...",
                    noResults: () => "Нет совпадений"
                }
            });

            // Устанавливаем город пользователя
            if (userCityId) {
                citySelect.val(userCityId).trigger('change');
            }
        });

    // 🔹 Обработка выбора города
    citySelect.on('change', function() {
        const value = $(this).val();

        if (value === 'add_new_city') {
            newCityFields.style.display = 'block';
            const option = new Option('+ Моего города нет в списке', 'add_new_city', true, true);
            citySelect.append(option).trigger('change.select2');
        } else {
            newCityFields.style.display = 'none';
            if (value) {
                // Автоматически сохраняем выбранный город
                fetch('{{ route('account.updateCity') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ city_id: value })
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        alert('Ошибка при сохранении города');
                    }
                })
                .catch(() => alert('Ошибка при сохранении города'));
            }
        }
    });

    // 🔹 Сохранение нового города
    saveCityBtn.addEventListener('click', function() {
        const name = document.getElementById('new-name').value.trim();
        const country = document.getElementById('new-country').value.trim();
        const region = document.getElementById('new-region').value.trim();

        if (!name || !country || !region) {
            alert('Пожалуйста, заполните все поля.');
            return;
        }

        fetch('{{ route('cities.add') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name, country, region })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const newOption = new Option(data.city.name, data.city.id, true, true);
                citySelect.append(newOption).trigger('change');
                newCityFields.style.display = 'none';

                // Автоматически сохраняем новый город
                fetch('{{ route('account.updateCity') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ city_id: data.city.id })
                });

                alert('Город успешно добавлен и выбран!');
            } else {
                alert('Ошибка при добавлении города');
            }
        })
        .catch(() => alert('Ошибка при добавлении города'));
    });
});
</script>




</body>
</html>
@endsection
