@vite(['resources/css/main.css','resources/sass/app.scss', 'resources/js/app.js'])
@extends('layouts.app')

@section('content')
<style>
    body.body_page_reset_password {
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
        margin: 0 auto 15px;
    }

    h2 {
        font-size: 22px;
        color: #333;
        margin-bottom: 25px;
    }

    .login_input {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #ccc;
        border-radius: 6px;
        margin-bottom: 14px;
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

    .register-btn {
        display: inline-block;
        margin-top: 18px;
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

    .alert-danger {
        color: #e74c3c;
        background-color: #fdecea;
        border: 1px solid #f5c2c0;
        border-radius: 6px;
        padding: 8px 10px;
        font-size: 13px;
        margin-bottom: 10px;
        text-align: left;
    }

    .navbar {
        display: none !important;
    }

    @media (max-width: 480px) {
        .login-page {
            padding: 30px 25px;
            border-radius: 0;
            box-shadow: none;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .page-login_logo {
            width: 100px;
            margin-bottom: 10px;
        }

        h2 {
            font-size: 20px;
        }

        .login_btn_page {
            font-size: 15px;
            padding: 10px;
        }

        .register-btn {
            font-size: 14px;
            padding: 8px 16px;
        }
    }
</style>

<body class="body_page_reset_password">
    <div class="login-page">
        <a href="{{ url('/') }}">
            <img class="page-login_logo" title="Перейти на сайт" src="{{ Storage::url('storage/logo/logo3.png') }}" alt="Логотип зверозор">
        </a>

        <h2>Сброс пароля</h2>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <input id="email" type="email"
                   class="login_input @error('email') is-invalid @enderror"
                   name="email"
                   value="{{ $email ?? old('email') }}"
                   placeholder="Email"
                   required autofocus>

            @error('email')
                <div class="alert alert-danger" role="alert">
                    {{ $message }}
                </div>
            @enderror

            <input id="password" type="password"
                   class="login_input @error('password') is-invalid @enderror"
                   name="password"
                   placeholder="Новый пароль"
                   required>

            @error('password')
                <div class="alert alert-danger" role="alert">
                    {{ $message }}
                </div>
            @enderror

            <input id="password-confirm" type="password"
                   class="login_input"
                   name="password_confirmation"
                   placeholder="Повторите пароль"
                   required>

            <button type="submit" class="login_btn_page">
                Сохранить новый пароль
            </button>
        </form>

        <a href="{{ route('login') }}" class="register-btn">Назад к входу</a>
    </div>
</body>
@endsection
