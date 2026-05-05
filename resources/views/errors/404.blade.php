@extends('layouts.app') {{-- Или тот лейаут, где у тебя @include('layouts.header') --}}

@section('content')
<div class="container text-center d-flex align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="error-content">
        {{-- Можно использовать изображение ко тика или собачки из твоих ассетов --}}
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
        
        <h1 class="display-1 fw-bold" style="color: #ff8c00;">404</h1>
        <h2 class="mb-4">Упс! Страница была съедена...</h2>
        <p class="lead mb-5 text-muted">
            Похоже, эта страница спряталась так же хорошо, как кот перед походом к ветеринару. <br>
            Попробуйте вернуться на главную или воспользоваться поиском.
        </p>
        
        <div class="d-flex justify-content-center gap-3">
            <a href="/" class="btn btn-primary btn-lg px-5" style="border-radius: 25px; background-color: #ff8c00; border: none;">
                На главную
            </a>
            <button onclick="window.history.back()" class="btn btn-outline-secondary btn-lg px-5" style="border-radius: 25px;">
                Назад
            </a>
        </div>

        {{-- Ссылка на объявления, так как это важный раздел --}}
        <div class="mt-5">
            <p>Ищете что-то конкретное? <a href="/ads" class="text-decoration-none" style="color: #ff8c00;">Посмотрите объявления</a></p>
        </div>
    </div>
</div>

<style>
    .error-content h1 {
        font-family: 'Arial Black', sans-serif;
        text-shadow: 2px 2px 0px #fff, 4px 4px 0px rgba(0,0,0,0.1);
    }
    body {
        background-color: #f1f1f1; /* В тон твоему инпуту поиска */
    }
</style>
@endsection