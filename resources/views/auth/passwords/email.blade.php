@vite(['resources/css/main.css','resources/sass/app.scss', 'resources/js/app.js'])
@extends('layouts.app')

@section('content')


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
            margin: 0 auto 10px;
        }

        h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
        }

        .forgot_input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 12px;
            font-size: 15px;
            transition: border-color 0.2s;
        }

        .forgot_input:focus {
            outline: none;
            border-color: #3399ff;
        }

        .forgot_btn {
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

        .forgot_btn:hover {
            background-color: #29b765;
        }

        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .status-message {
            color: #28a745;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .error-message {
            color: #e74c3c;
            font-size: 13px;
            margin-bottom: 10px;
            text-align: left;
        }

        .navbar {
            display: none !important;
        }
    </style>



<body class="body_page">
    <div class="login-page">
        <a href="{{ url('/') }}">
            <!-- <img class="page-login_logo" src="{{ Storage::url('logo3.png') }}" alt="Зверополис"> -->
        </a>

        <h2>Восстановление пароля</h2>

        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
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
                <div class="alert alert-danger" role="alert">
                    {{ $message }}
                </div>
            @enderror
<br>
            <button type="submit" class="login_btn_page">
                Отправить ссылку для сброса пароля
            </button>
        </form>

        <a href="{{ route('login') }}" class="register-btn">Назад к входу</a>
    </div>
</body>
@endsection
