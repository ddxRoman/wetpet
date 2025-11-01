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
    padding: 0;
    min-height: 100vh;
}

/* 🔹 Навбар */
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #ffffff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    padding: 12px 40px;
    position: sticky;
    top: 0;
    z-index: 100;
}

.navbar-logo img {
    height: 45px;
    width: auto;
    transition: transform 0.2s ease;
}

.navbar-logo img:hover {
    transform: scale(1.05);
}

.navbar-user {
    font-weight: 600;
    color: #333;
}

/* Основной контейнер */
.account-container {
    display: flex;
    background: #fff;
    width: 90%;
    max-width: 1600px;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    margin: 40px auto;
    transition: all 0.3s ease;
}

/* Левая панель */
.sidebar {
    width: 25%;
    min-width: 280px;
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
    padding: 50px 80px;
    max-width: 1000px;
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
    align-items: center;
    gap: 8px;
}

.checkbox-group input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.checkbox-group label {
    font-weight: 500;
    color: #444;
    margin: 0;
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
    .navbar {
        padding: 10px 20px;
    }
    .navbar-logo img {
        height: 35px;
    }
    .account-container {
        flex-direction: column;
        margin: 20px 10px;
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
    </style>
</head>

<body class="body_page">
    {{-- 🔹 Навбар с логотипом --}}
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
                <form method="POST" action="{{ route('account.updateProfile') }}" enctype="multipart/form-data">
                    @csrf

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
                        <input type="text" name="city" value="{{ $user->city->name ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label>Почта *</label>
                        <input type="email" name="email" value="{{ $user->email }}" required>
                    </div>

                    <div class="form-group">
                        <label>Дата регистрации</label>
                        <input type="text" value="{{ $user->created_at->format('d.m.Y') }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Аватар</label>
                        <input type="file" name="avatar" accept="image/*">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" class="avatar-preview" alt="Аватар">
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
