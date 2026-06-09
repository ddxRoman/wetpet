@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            
            {{-- Хлебные крошки / Навигация назад --}}
            <div class="mb-4">
                <a href="{{ url('/') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 shadow-sm text-decoration-none">
                    <img src="{{ asset('storage/icon/button/arrow-back.svg') }}" width="22" alt="back">
                    На главную
                </a>
            </div>

            {{-- Карточка с контентом политики --}}
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-4 p-md-5">
                    
                    <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                        <div class="p-3 rounded-circle bg-light d-inline-block">
                            🍪
                        </div>
                        <h1 class="fw-bold m-0" style="font-size: 2rem;">Политика использования файлов Cookie</h1>
                    </div>

                    <p class="text-muted small mb-4">Дата последнего обновления: 1 июня 2026 г.</p>


                    
                    <div class="cookie-text-content text-secondary lh-lg">
<p>
    Настоящая Политика использования файлов cookie определяет условия, на которых 
    <strong>{{ config('company.fio') }}</strong> (ИНН: {{ config('company.inn') }}), 
    именуемый в дальнейшем «Оператор», использует файлы cookie на интернет-сайте «Зверозор».
</p>
                        <h3 class="fw-semibold text-dark mt-4 h5">1. Что такое файлы cookie?</h3>
                        <p>Cookie — это небольшие текстовые файлы, которые сохраняются на вашем компьютере или мобильном устройстве при посещении сайтов. Они широко используются для обеспечения работы веб-сайтов или повышения эффективности их работы, а также для получения аналитической информации.</p>

                        <h3 class="fw-semibold text-dark mt-4 h5">2. Как мы используем файлы cookie?</h3>
                        <p>Мы используем файлы cookie для нескольких целей:</p>
                        <ul>
                            <li><strong>Технические (Обязательные):</strong> Необходимы для корректной работы авторизации пользователей, сохранения выбранного вами города в шапке сайта и работы модальных окон добавления специалистов или организаций.</li>
                            <li><strong>Аналитические:</strong> Помогают нам понять, как пользователи взаимодействуют со страницами объявлений и каталогами животных, чтобы делать интерфейс удобнее.</li>
                        </ul>

                        <h3 class="fw-semibold text-dark mt-4 h5">3. Управление файлами cookie</h3>
                        <p>Вы имеете право решить, принимать файлы cookie или отклонять их. Вы можете настроить параметры своего веб-браузера так, чтобы он принимал или блокировал любые cookie-файлы. Обратите внимание, что отключение обязательных файлов cookie может нарушить работу личного кабинета и некоторых интерактивных функций сайта.</p>

                        <h3 class="fw-semibold text-dark mt-4 h5">4. Изменения в Политике</h3>
                        <p>Мы можем периодически обновлять настоящую Политику использования файлов cookie в связи с изменениями в технологических процессах или требованиях законодательства. Пожалуйста, регулярно проверяйте эту страницу, чтобы оставаться в курсе наших правил.</p>
                    </div>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-0 small text-muted">Остались вопросы по работе сайта? Напишите нам через вкладку «Контакты».</p>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

@endsection