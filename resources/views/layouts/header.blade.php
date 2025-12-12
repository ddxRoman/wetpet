@include('account.modals.modal-add-specialist', ['cities' => $cities])
@include('account.modals.modal-add-organization', ['cities' => $cities])

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ url('favicon.ico') }}" type="image/vnd.microsoft.icon">
    <meta name="robots" content="index, follow">

    @if(Route::currentRouteName() === 'clinics.show')
        <meta name="description" content="Узнать стоимость услуг, посмотреть график работы прочитать и оставить отзывы на ветеринарную клинику">
        <title>{{ $clinic->name ? $clinic->name . ' — контакты и отзывы о клинике в городе '  : 'Сайт про домашних животных' }}</title>

    @elseif(Route::currentRouteName() === 'doctors.show')
        <meta name="description" content="Узнать стоимость услуг, записаться на приём, прочитать и оставить отзывы на ветеринарного врача">
        <title>{{ $doctor->name ? $doctor->name . ' — ветеринар в городе '  : 'Сайт про домашних животных' }}</title>

    @else
        <meta name="description" content="Зверозор прочитать отзывы о домашних животных, ветеринарных клиниках, и врачах делимся опытом">
        <title>{{ $brandname ?? 'Сайт про домашних животных' }}</title>
    @endif

    @vite(['resources/css/main.css', 'resources/sass/app.scss', 'resources/js/app.js'])

</head>


<body class="body_page">
<header class="site-header 
        {{ request()->is('clinics*') ? 'compact-header' : '' }}
        {{ request()->is('doctors*') ? 'compact-header' : '' }}">
    
    <div class="container py-2">

        {{-- =================== HEADER MAIN ROW =================== --}}
        <div class="header-grid">

            {{-- ==== Левый блок (город) ==== --}}
            <div class="city-block">
                @include('partials.city-selector')
            </div>

            {{-- ==== Логотип ==== --}}
            <div class="logo-block">
                <a href="/" class="header-logo-link">
                    <img class="header_logo" src="{{ Storage::url('logo/logo3.png') }}" alt="{{ $brandname }}">
                </a>
            </div>

            {{-- ==== Правый блок (кнопки + профиль) ==== --}}
            <div class="right-block d-flex align-items-center gap-3">

                {{-- Кнопки --}}
                <div class="d-none d-md-flex gap-2">
                    <button class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#addOrganizationModal">
                        <img class="add_btn"  src="{{ Storage::url('icon/button/add_clinic_btn.png') }}" title="Добавить организацию" alt="Добавить организацию">
                    </button>

                    <button class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
                        <img class="add_btn" src="{{ Storage::url('icon/button/add_doctor_btn.png') }}" title="Добавить специалиста" alt="Добавить специалиста">
                    </button>
                </div>

                {{-- Профиль --}}
                <div class="d-flex align-items-center">
                    @guest
                        <a href="{{ route('login') }}" class="login_link">
                            <button type="button" class="btn_login">Войти</button>
                        </a>
                    @endguest

                    @auth
                        @php
                            $randomNumber = rand(0, 20);
                            $link = "storage/avatars/default/$randomNumber.png";
                        @endphp

                        <div class="dropdown">
                            <a id="navbarDropdown"
                               class="profile_link dropdown-toggle d-flex align-items-center gap-2"
                               href="#"
                               role="button"
                               data-bs-toggle="dropdown">
                                <img class="avatars_pics" src="{{ asset($link) }}" alt="Аватар">
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('account') }}">Профиль</a>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Выйти
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    @endauth
                </div>

            </div>

        </div>

        {{-- =================== БЛОК ПОИСКА И ОПИСАНИЯ =================== --}}
        @if(!request()->is('clinics*') && !request()->is('doctors*'))

            <div class="text-center mt-3">
                <h1>Сайт про домашних животных</h1>
                <p>
                    На сайте вы сможете найти ветеринарные клиники, ветгостиницы, лекарства, ветеринаров и грумеров,<br>
                    а также прочесть отзывы о породах от владельцев.
                </p>
            </div>

            <div class="d-flex justify-content-center mt-3">
                <input type="search" class="header-search" placeholder="Животные, породы, ветеринары, клиники">

                    <img class="btn_search" src="{{ Storage::url('icon/button/search.svg') }}" alt="Поиск">

            </div>

        @endif

    </div>

</header>

<style>

</style>