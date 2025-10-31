@extends('layouts.app')

@section('content')
<style>
    body {
        background-color: #eef3ff;
        font-family: "Segoe UI", Arial, sans-serif;
        margin: 0;
        padding: 0;
    }

    /* убираем верхнее меню Login / Register */
    .navbar {
        display: none !important;
    }

    .register-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 90vh; /* растягиваем контейнер на всю высоту */
        text-align: center;
        box-sizing: border-box;
    }

    .register-box {
        background-color: #fff;
        padding: 40px 60px;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        max-width: 400px;
        width: 100%;
    }

    .register-logo {
        display: block;
        margin: 0 auto 25px;
        max-height: 80px;
    }

    .register-title {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 30px;
        color: #333;
    }

    .register-box label {
        display: block;
        text-align: left;
        font-size: 14px;
        color: #444;
        margin-bottom: 4px;
    }

    .register-box label span {
        color: #e74c3c;
        font-weight: bold;
        margin-left: 3px;
    }

    .register-box input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        margin-bottom: 12px;
        font-size: 15px;
        transition: border-color 0.2s;
    }

    .register-box input:focus {
        outline: none;
        border-color: #3399ff;
    }

    .error-message {
        color: #e74c3c;
        font-size: 13px;
        text-align: left;
        margin-bottom: 10px;
    }

    .register-button {
        width: 100%;
        background-color: #2ecc71;
        color: #fff;
        border: none;
        padding: 12px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .register-button:hover {
        background-color: #29b765;
    }

    .login-link {
        margin-top: 25px;
        font-size: 14px;
        color: #444;
    }

    .login-link a {
        color: #3399ff;
        text-decoration: none;
        font-weight: 500;
    }

    .login-link a:hover {
        text-decoration: underline;
    }

    .page-registr_logo {
        width: 25%;
    }
</style>

<div class="register-container">
    <div class="register-box">
        <a href="{{ url('/') }}">
            <img class="page-registr_logo" src="{{ Storage::url('logo3.png') }}" alt="Зверополис">
        </a>
        <div class="register-title">Регистрация</div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <label for="name">Имя<span>*</span></label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
            @error('name')
                <div class="error-message">{{ $message }}</div>
            @enderror

            <label for="email">Email<span>*</span></label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>
            @error('email')
                <div class="error-message">{{ $message }}</div>
            @enderror

            <label for="phone">Телефон</label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}">
            @error('phone')
                <div class="error-message">{{ $message }}</div>
            @enderror

            <label for="password">Пароль<span>*</span></label>
            <input id="password" type="password" name="password" required>
            @error('password')
                <div class="error-message">{{ $message }}</div>
            @enderror

            <label for="password-confirm">Подтвердите пароль</label>
            <input id="password-confirm" type="password" name="password_confirmation" required>

            <button type="submit" class="register-button">Зарегистрироваться</button>
        </form>

        <div class="login-link">
            Уже зарегистрированы? <a href="{{ route('login') }}">Войти</a>
        </div>
    </div>
</div>
@endsection
