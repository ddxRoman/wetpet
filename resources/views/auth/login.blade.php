@extends('layouts.app')

@section('content')
<div class="body_page_auth">
    <div class="login-page">

        <a href="{{ url('/') }}">
            <img class="page-login_logo" src="{{ asset('storage/logo/logo3.png') }}" alt="{{ $brandname ?? 'Зверозор' }}"
>
        </a>

        <a href="{{ route('register') }}" class="register-btn">Зарегистрироваться</a>
    @if ($errors->any())
    <div class="text-red-500 mt-2">
        {{ $errors->first('email') }}
    </div>
@endif
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

        <!-- <h3>или авторизуйтесь через:</h3>
        <div>
            <a href="#"><img class="login_social_icon" src="{{ Storage::url('icon/social/telegram-logo.svg') }}" title="Telegram" alt="Telegram"></a>
            <a href="#"><img class="login_social_icon" src="{{ Storage::url('icon/social/vk-logo.svg') }}" title="VK ID" alt="VK"></a>
            <a href="#"><img class="login_social_icon" src="{{ Storage::url('icon/social/yandex-logo.svg') }}" title="Yandex" alt="Yandex"></a>
        </div> -->
    </div>


</div>
@endsection

