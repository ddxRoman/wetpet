@extends('layouts.app')

@section('content')
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

            <button type="submit" class="login_btn_page">
                Отправить ссылку для сброса пароля
            </button>
        </form>

        <a href="{{ route('login') }}" class="register-btn">Назад к входу</a>
    </div>
</body>
@endsection
