@extends('layouts.app') {{-- Как и 404 — через общий layout, доступ запрещён не говорит о сбое сайта --}}

@section('content')
<div class="container text-center d-flex align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="error-content">
        <div class="error-image mb-4">
<video
    src="{{ asset('storage/video/err_cat.mp4') }}"
    autoplay
    muted
    loop
    playsinline
    class="img-fluid"
    style="max-width: 100%; border-radius: 15px;"
    poster="{{ asset('storage/images/err_cat_placeholder.jpg') }}">
</video>
</div>

        <h1 class="display-1 fw-bold" style="color: #ff8c00;">403</h1>
        <h2 class="mb-4">Сюда нельзя</h2>
        <p class="lead mb-5 text-muted">
            Этот кот охраняет раздел, на который у вас нет доступа. <br>
            Если вы уверены, что это ошибка — проверьте, под тем ли аккаунтом вы вошли.
        </p>

        <div class="d-flex justify-content-center gap-3">
            <a href="/" class="btn btn-primary btn-lg px-5" style="border-radius: 25px; background-color: #ff8c00; border: none;">
                На главную
            </a>
            <button onclick="window.history.back()" class="btn btn-outline-secondary btn-lg px-5" style="border-radius: 25px;">
                Назад
            </button>
        </div>

        <div class="mt-5">
            @auth
                <p>Хотели попасть в свой кабинет? <a href="{{ route('account') }}" class="text-decoration-none" style="color: #ff8c00;">Личный кабинет</a></p>
            @else
                <p>Доступ может быть открыт после входа. <a href="{{ route('login') }}" class="text-decoration-none" style="color: #ff8c00;">Войти</a></p>
            @endauth
        </div>
    </div>
</div>

<style>
    .error-content h1 {
        font-family: 'Arial Black', sans-serif;
        text-shadow: 2px 2px 0px #fff, 4px 4px 0px rgba(0,0,0,0.1);
    }
    body {
        background-color: #f1f1f1;
    }
</style>
@endsection
