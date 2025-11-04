@extends('layouts.app')
@vite(['resources/css/main.css','resources/sass/app.scss', 'resources/js/app.js'])

@if(Auth::check())
    <script>window.location.href = "{{ url('/') }}";</script>
@endif



<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <style>
        body.body_page {
            background-color: #eef3ff;
            font-family: "Segoe UI", Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .login-page {
            text-align: center;
            width: 100%;
            max-width: 400px;
            background-color: #fff;
            padding: 40px 50px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .page-login_logo {
            width: 120px;
            display: block;
            margin: 0 auto 10px;
        }

        .register-btn {
            display: inline-block;
            margin-bottom: 20px;
            background-color: #ec7819b6;
            color: #fff;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .register-btn:hover {
            background-color: #ec7819ff;
        }

        .login_input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 12px;
            font-size: 15px;
            transition: border-color 0.2s;
        }

        .login_input:focus {
            outline: none;
            border-color: #3399ff;
        }

        .login_btn_page {
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

        .login_btn_page:hover {
            background-color: #29b765;
        }

        .error-message {
            color: #e74c3c;
            font-size: 13px;
            text-align: left;
            margin-top: -8px;
            margin-bottom: 10px;
        }

        .forgot-btn {
            display: inline-block;
            margin-top: 8px;
            color: #007bff;
            font-size: 14px;
            text-decoration: none;
            transition: color 0.2s;
        }

        .forgot-btn:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .hr_login_page {
            margin: 25px 0;
            border: none;
            border-top: 1px solid #ddd;
        }

        h3 {
            font-size: 16px;
            font-weight: 500;
            color: #444;
            margin-bottom: 15px;
        }

        .login_social_icon {
            width: 40px;
            height: 40px;
            margin: 0 6px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .login_social_icon:hover {
            transform: scale(1.1);
        }

        .remember-block {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 15px;
            justify-content: center;
            color: #555;
            font-size: 14px;
        }

        .navbar {
            display: none !important; /* скрываем Login / Register */
        }
    </style>
</head>

@section('content')
<body class="body_page">
    <div class="login-page">

        <a href="{{ url('/') }}">
            <img class="page-login_logo" src="{{ Storage::url('logo/logo3.png') }}" alt="{{$brandname}}">
        </a>

        <a href="{{ route('register') }}" class="register-btn">Зарегистрироваться</a>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Поле Email --}}
            <input class="login_input" id="login" type="text" name="login" placeholder="Email" value="{{ old('login') }}" required autofocus>
            @error('login')
                <div class="error-message">{{ $message }}</div>
            @enderror
            @if (session('login_error'))
                <div class="error-message">{{ session('login_error') }}</div>
            @endif

            {{-- Поле Пароль --}}
            <input class="login_input" type="password" id="password" name="password" placeholder="Пароль" required>
            @error('password')
                <div class="error-message">{{ $message }}</div>
            @enderror
            @if (session('password_error'))
                <div class="error-message">{{ session('password_error') }}</div>
            @endif

            {{-- Если пароль неверный, показываем ссылку "Забыли пароль?" --}}
            @if ($errors->has('password') || session('password_error'))
                <a href="{{ route('password.request') }}" class="forgot-btn">Забыли пароль?</a>
            @endif

            <div class="remember-block">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Запомнить меня</label>
            </div>

            <button class="login_btn_page">Войти</button>
        </form>

        <hr class="hr_login_page">

        <h3>или авторизуйтесь через:</h3>
        <div>
            <a href="#"><img class="login_social_icon" src="{{ Storage::url('icon/social/telegram-logo.svg') }}" title="Telegram" alt="Telegram"></a>
            <a href="#"><img class="login_social_icon" src="{{ Storage::url('icon/social/vk-logo.svg') }}" title="VK ID" alt="VK"></a>
            <a href="#"><img class="login_social_icon" src="{{ Storage::url('icon/social/yandex-logo.svg') }}" title="Yandex" alt="Yandex"></a>
        </div>
    </div>
</body>
@endsection
</html>
