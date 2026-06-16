@vite(['resources/css/main.css', 'resources/css/mobile.css', 'resources/sass/app.scss', 'resources/js/app.js'])
@extends('layouts.app')

@section('content')
@include('layouts.header')
<div class="container py-5">
    {{-- Главный баннер / Заголовок --}}
    <div class="row justify-content-center text-center mb-5">
        <div class="col-lg-9">
            <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold mb-3">Партнерство со Зверозор</span>
            <h1 class="fw-bold display-5 mb-3">Развивайте ваш зообизнес вместе с нами</h1>
            <p class="text-muted lead">
                Зверозор — это уникальная экосистема, объединяющая владельцев животных, ветеринарных специалистов и зооорганизации. Присоединяйтесь к нам, чтобы привлечь целевую аудиторию и заявить о себе.
            </p>
        </div>
    </div>

    {{-- Цифры и преимущества --}}
    <div class="row g-4 mb-5 text-center">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 p-4">
                <div class="card-body">
                    <h2 class="fw-bold text-primary mb-2">> 50 000</h2>
                    <p class="text-dark fw-semibold mb-1">Уникальных посетителей</p>
                    <small class="text-muted">Ежемесячно ищут ветеринаров, клиники и информацию о породах.</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 p-4">
                <div class="card-body">
                    <h2 class="fw-bold text-success mb-2">100%</h2>
                    <p class="text-dark fw-semibold mb-1">Целевая аудитория</p>
                    <small class="text-muted">Никакого «пустого» трафика — только любящие владельцы домашних питомцев.</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0 p-4">
                <div class="card-body">
                    <h2 class="fw-bold text-warning mb-2">Удобно</h2>
                    <p class="text-dark fw-semibold mb-1">Filament-админка</p>
                    <small class="text-muted">Управляйте своими услугами, прайс-листами и контактами в один клик.</small>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-5 opacity-25">

    {{-- Кому подходит партнерство --}}
    <div class="row align-items-center g-5 mb-5">
        <div class="col-lg-6">
            <h2 class="fw-bold mb-4">Кому подходит размещение на Зверозор?</h2>
            
            <div class="d-flex align-items-start mb-4">
                <div class="bg-primary text-white rounded p-2 me-3 shadow-sm">
                    <i class="bi bi-heart-pulse-fill fs-4 px-1"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">Ветеринарным клиникам и кабинетам</h5>
                    <p class="text-muted mb-0">Публикуйте актуальный прайс-лист на услуги, собирайте отзывы клиентов и продвигайте своих врачей.</p>
                </div>
            </div>

            <div class="d-flex align-items-start mb-4">
                <div class="bg-success text-white rounded p-2 me-3 shadow-sm">
                    <i class="bi bi-person-bounding-box fs-4 px-1"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">Частным специалистам</h5>
                    <p class="text-muted mb-0">Грумеры, кинологи, зоопсихологи и хендлеры могут создать персональную страницу-визитку и находить новых заказчиков.</p>
                </div>
            </div>

            <div class="d-flex align-items-start mb-4">
                <div class="bg-info text-white rounded p-2 me-3 shadow-sm">
                    <i class="bi bi-shop fs-4 px-1"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">Зоомагазинам и брендам кормов</h5>
                    <p class="text-muted mb-0">Интегрируйте ваши товары, запускайте рекламные баннеры на страницах популярных пород или в каталоге объявлений.</p>
                </div>
            </div>
        </div>

        {{-- Форма или карточка призыва к действию --}}
        <div class="col-lg-6">
            <div class="card shadow border-0 p-4 bg-light">
                <div class="card-body">
                    <h3 class="fw-bold mb-3">Стать партнером</h3>
                    <p class="text-muted mb-4">
                        Заполните форму, и мы поможем вам бесплатно настроить профиль организации или специалиста на нашем портале.
                    </p>
                    
                    <form action="#" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ваше имя / Название компании</label>
                            <input type="text" class="form-control" placeholder="Иван Иванов / Ветеринарный центр" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Электронная почта</label>
                            <input type="email" class="form-control" placeholder="partner@example.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Номер телефона</label>
                            <input type="tel" class="form-control" placeholder="+7 (999) 999-99-99" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Сфера деятельности</label>
                            <select class="form-select">
                                <option>Ветеринарная клиника</option>
                                <option>Частный врач / Специалист</option>
                                <option>Зоомагазин / Производство</option>
                                <option>Другое</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold shadow-sm mt-2">
                            Отправить заявку
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection