@extends('layouts.app')
@vite(['resources/css/main.css','resources/sass/app.scss', 'resources/js/app.js'])

@section('content')
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <style>
body.body_page {
    background-color: #eef3ff;
    font-family: "Segoe UI", Arial, sans-serif;
    margin: 0;
    padding: 40px 0;

    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
}

.account-container {
    display: flex;
    background: #fff;
    width: 90%;              /* Занимает 90% ширины экрана */
    max-width: 1600px;       /* Ограничим для больших мониторов */
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    margin: 0 auto;
    transition: all 0.3s ease;
}

/* Левая панель */
.sidebar {
    width: 25%;              /* Займёт четверть ширины */
    min-width: 280px;        /* Чтобы не была слишком узкой */
    background-color: #f6f8fc;
    border-right: 1px solid #e0e4f1;
    padding: 30px 0;
}

        .sidebar button {
            display: block;
            width: 100%;
            padding: 16px 25px;
            text-align: left;
            font-size: 16px;
            color: #444;
            border: none;
            background: none;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .sidebar button:hover,
        .sidebar button.active {
            background-color: #e3e9ff;
            color: #0066ff;
            font-weight: 600;
        }

        /* Контент */
.account-content {
    flex: 1;
    padding: 50px 80px;      /* Просторнее */
    max-width: 1000px;       /* Контент не растягивается слишком */
    margin: 0 auto;
}

        h2 {
            font-size: 22px;
            color: #333;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
            text-align: left;
        }

        label {
            display: block;
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="file"],
        select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }

        input:focus,
        select:focus {
            border-color: #007bff;
            outline: none;
        }

.checkbox-group {
    margin-top: 10px;
    display: flex;
    align-items: center; /* Центрирует по вертикали */
    gap: 8px;            /* Расстояние между чекбоксом и текстом */
}

.checkbox-group input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.checkbox-group label {
    font-weight: 500;
    color: #444;
    margin: 0;           /* Убираем старый отступ */
    cursor: pointer;
}


        .save-btn {
            display: inline-block;
            background-color: #2ecc71;
            color: #fff;
            font-weight: 600;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 20px;
        }

        .save-btn:hover {
            background-color: #29b765;
        }

        .avatar-preview {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e5e9f0;
            margin-top: 12px;
        }

        /* 📱 Мобильная версия */
        @media (max-width: 768px) {
            body.body_page {
                padding: 20px 0;
            }
            .account-container {
                flex-direction: column;
                margin: 0 10px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            .sidebar {
                width: 100%;
                display: flex;
                justify-content: space-around;
                border-right: none;
                border-bottom: 1px solid #ddd;
                padding: 10px 0;
            }
            .sidebar button {
                text-align: center;
                font-size: 14px;
                padding: 12px;
            }
            .account-content {
                padding: 25px 20px;
            }
        }

        /* 🖥️ Для больших экранов — немного “воздуха” по бокам */
        @media (min-width: 1400px) {
            .account-container {
                max-width: 1400px;
            }
            .account-content {
                padding: 60px 80px;
            }
        }
    </style>
</head>

<body class="body_page">
    <div class="account-container">
        {{-- Боковое меню --}}
        <div class="sidebar">
            <button class="tab-btn active" data-tab="profile">Профиль</button>
            <button class="tab-btn" data-tab="pets">Питомцы</button>
        </div>

        {{-- Контент --}}
        <div class="account-content">
            {{-- Вкладка Профиль --}}
            <div class="tab-content" id="profile">
                <h2>Профиль</h2>
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label>ФИО *</label>
                        <input type="text" name="name" value="{{ Auth::user()->name }}" required>
                    </div>

                    <div class="form-group">
                        <label>Ник *</label>
                        <input type="text" name="nickname" value="{{ Auth::user()->nickname ?? '' }}" required>
                    </div>

                    <div class="form-group">
                        <label>Дата рождения</label>
                        <input type="date" name="birth_date" value="{{ Auth::user()->birth_date ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label>Город</label>
                        <input type="text" name="city" value="{{ Auth::user()->city->name ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label>Почта *</label>
                        <input type="email" name="email" value="{{ Auth::user()->email }}" required>
                    </div>

                    <div class="form-group">
                        <label>Дата регистрации</label>
                        <input type="text" value="{{ Auth::user()->created_at->format('d.m.Y') }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Аватар</label>
                        <input type="file" name="avatar" accept="image/*">
                        @if(Auth::user()->avatar)
                            <img src="{{ Storage::url(Auth::user()->avatar) }}" class="avatar-preview" alt="Аватар">
                        @endif
                    </div>

                    <button type="submit" class="save-btn">Сохранить изменения</button>
                </form>
            </div>

            {{-- Вкладка Питомцы --}}
            <div class="tab-content" id="pets" style="display:none;">
                <h2>Питомцы</h2>
                <form action="{{ route('pets.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label>Вид животного *</label>
                        <input type="text" name="type" required>
                    </div>

                    <div class="form-group">
                        <label>Порода *</label>
                        <input type="text" name="breed" required>
                    </div>

                    <div class="form-group">
                        <label>Дата рождения</label>
                        <input type="date" id="pet_birth_date" name="birth_date">

                        <div class="checkbox-group">
                            <input type="checkbox" id="unknown_date">
                            <label for="unknown_date">Не знаю точной даты</label>
                        </div>
                    </div>

                    <div class="form-group" id="age_field" style="display:none;">
                        <label>Возраст животного</label>
                        <input type="text" name="age" placeholder="Например: 3 года">
                    </div>

                    <div class="form-group">
                        <label>Окрас</label>
                        <input type="text" name="color">
                    </div>

                    <div class="form-group">
                        <label>Фото</label>
                        <input type="file" name="photo" accept="image/*">
                    </div>

                    <button type="submit" class="save-btn">Добавить питомца</button>
                </form>
            </div>

@if (session('success'))
    <div style="color: green; margin-bottom: 15px;">
        {{ session('success') }}
    </div>
@endif


        </div>
    </div>

    <script>
        // Переключение вкладок
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                tabBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                tabContents.forEach(c => c.style.display = 'none');
                document.getElementById(btn.dataset.tab).style.display = 'block';
            });
        });

        // Логика чекбокса "Не знаю точной даты"
        const unknownDateCheckbox = document.getElementById('unknown_date');
        const ageField = document.getElementById('age_field');
        const birthInput = document.getElementById('pet_birth_date');

        unknownDateCheckbox.addEventListener('change', () => {
            if (unknownDateCheckbox.checked) {
                ageField.style.display = 'block';
                birthInput.disabled = true;
            } else {
                ageField.style.display = 'none';
                birthInput.disabled = false;
            }
        });
    </script>
</body>
</html>
@endsection
