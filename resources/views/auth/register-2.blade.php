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

    .consent-row {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        text-align: left;
        margin-bottom: 16px;
    }

    .consent-row input[type="checkbox"] {
        width: 16px;
        height: 16px;
        margin-top: 3px;
        flex-shrink: 0;
        accent-color: #2ecc71;
        cursor: pointer;
    }

    .consent-label {
        font-size: 13px;
        color: #555;
        line-height: 1.4;
        cursor: pointer;
    }

    .consent-label a {
        color: #3399ff;
        text-decoration: none;
    }

    .consent-label a:hover {
        text-decoration: underline;
    }
</style>
<title>Регистрация</title>
<div class="register-container">
    <div class="register-box">
        <a href="{{ url('/') }}">
            <img class="page-registr_logo" title="перейти на сайт" src="{{ Storage::url('logo/logo3.png') }}" alt="{{$brandname}}">
        </a>
        <div class="register-title">Регистрация</div>

<h2>К сожалению на данным момент регистрация недоступна</h2>

        <div class="login-link">
            Уже зарегистрированы? <a href="{{ route('login') }}" title="Авторизоваться">Войти</a>
        </div>
    </div>
</div>
@endsection
