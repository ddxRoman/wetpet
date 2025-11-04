<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ url('favicon.ico') }}">
    <title>{{ $brandname ?? 'Сайт про домашних животных' }}</title>

    {{-- Подключение стилей и скриптов --}}
    @vite(['resources/css/main.css', 'resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body class="body_page">
<header class="site-header {{ request()->is('clinics*') ? 'compact-header' : '' }}">
    <div class="container h-100">
        <div class="row align-items-center h-100">

            {{-- Левая часть: выбор города --}}
            <div class="col-3 d-flex align-items-center justify-content-start">
                @include('partials.city-selector')
            </div>

            {{-- Центральная часть: логотип --}}
            <div class="col-6 text-center">
                <a href="/" class="header-logo-link">
                    <img class="header_logo" src="{{ Storage::url('logo/logo3.png') }}" alt="{{ $brandname }}">
                </a>
            </div>

            {{-- Правая часть: профиль / вход --}}
            <div class="col-3 d-flex justify-content-end align-items-center profile_block">
                @guest
                    <a class="login_link" href="{{ route('login') }}">
                        <button type="button" class="btn_login">Войти</button>
                    </a>
                @endguest

                @auth
                    @php
                        $randomNumber = rand(0, 20);
                        $link = "storage/avatars/default/$randomNumber.png";
                    @endphp
                    <div class="dropdown">
                        <a id="navbarDropdown" class="profile_link dropdown-toggle" href="#" role="button"
                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
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

        {{-- Блок описания и поиска (только НЕ для страниц /clinics и /clinics/*) --}}
        @if(!request()->is('clinics*'))
            <div class="description_view text-center mt-3">
                <h1>Сайт про домашних животных</h1>
                <p>
                    На сайте вы сможете найти ветеринарные клиники, ветгостиницы, лекарства, ветеринаров и грумеров,<br>
                    а также прочесть отзывы о породах от владельцев.
                </p>
            </div>

            <div class="row mt-3">
                <div class="col-12 d-flex justify-content-center align-items-center">
                    <input type="search" class="header-search" placeholder="Животные, породы, ветеринары, клиники">
                    <a href="#" class="btn_search_link">
                        <img class="btn_search" src="{{ Storage::url('icon/button/search.svg') }}" alt="Поиск">
                    </a>
                </div>
            </div>
        @endif
    </div>
</header>
</body>
</html>
