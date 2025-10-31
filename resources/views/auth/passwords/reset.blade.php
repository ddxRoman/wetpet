@extends('layouts.app')

@section('content')
<body class="body_page">
    <div class="login-page">
        <a href="{{ url('/') }}">
            <!-- <img class="page-login_logo" src="{{ Storage::url('logo3.png') }}" alt="Зверополис"> -->
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
