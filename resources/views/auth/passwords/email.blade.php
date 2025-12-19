@vite(['resources/css/main.css','resources/sass/app.scss', 'resources/js/app.js'])
@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Восстановление пароля</title>
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

        .forgot-page {
            text-align: center;
            width: 100%;
            max-width: 400px;
            background-color: #fff;
            padding: 40px 50px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .page-forgot_logo {
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
            background-color: #3cec19b6;
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
            background-color: #19ec58ff;
        }

        .register-btn {
            display: inline-block;
            margin-top: 15px;
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }

        .register-btn:hover {
            text-decoration: underline;
        }

        .alert-success {
            color: #28a745;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            padding: 10px;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .alert-danger {
            color: #e74c3c;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 10px;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .navbar {
            display: none !important;
        }
    </style>
</head>

<body class="body_page">
    <div class="forgot-page">
        <a href="{{ url('/') }}">
            <img class="page-forgot_logo" title="Перейти на сайт" src="{{ Storage::url('logo/logo3.png') }}" alt="{{ config('app.name') }}">
        </a>

        <h2>Восстановление пароля</h2>

        @if (session('status'))
            <div class="alert-success" role="alert">
                Мы отправили ссылку для сброса пароля на указанный адрес.
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <input id="email" type="email"
                   class="login_input @error('email') is-invalid @enderror"
                   name="email"
                   value="{{ old('email') }}"
                   placeholder="Введите ваш Email"
                   required autofocus>

            @error('email')
                <div class="alert-danger" role="alert">
                    {{ $message }}
                </div>
            @enderror
<br>
<button type="submit"
        class="login_btn_page"
        id="resetBtn">
    Отправить ссылку для сброса пароля
</button>
<div id="timerText" style="margin-top:10px;font-size:14px;color:#666;"></div>
        </form>

        <a href="{{ route('login') }}" title="Вернутся к странице авторизации" class="register-btn">Назад к входу</a>
    </div>

    <script>
    window.passwordResetSent = @json(session()->has('status'));
</script>

</body>
</html>
@endsection