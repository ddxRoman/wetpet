@include('account.modals.modal-add-specialist', ['cities' => $cities])
@include('account.modals.modal-add-organization', ['cities' => $cities])

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ url('favicon.ico') }}" type="image/vnd.microsoft.icon">
<meta name="robots" content="all"/>
<meta name="robots" content="index, follow"/>
<!--Робот выберет значение all, текст и ссылки будут проиндексированы.-->

    @if(Route::currentRouteName() === 'clinics.show')
        <meta name="description" content="Узнать стоимость услуг, посмотреть график работы прочитать и оставить отзывы на ветеринарную клинику">
        <title>{{ $clinic->name ? $clinic->name .' '. $currentCityName . ' — контакты и отзывы о клинике в городе '   : 'Сайт про домашних животных в твоём городе' }}</title>
    @elseif(Route::currentRouteName() === 'clinics.index')
        <meta name="description" content="Найти ветеринарную клинику в городе, узнать ретинг, прочитать отзывы, найти по услуге контакты клиники, ретинг и список врачей">
        <title>{{'Все ветеринарные клиники города '. $currentCityName}}</title>
    @elseif(Route::currentRouteName() === 'doctors.show')
        <meta name="description" content="Узнать стоимость услуг у специалиста, записаться на приём, информация об образовани и месте работы прочитать и оставить отзывы на ветеринарного врача в городе ">
        <title>{{ $doctor->name ? $doctor->name . ' —  информация о специалисте '  : 'Сайт про домашних животных' }}</title>
    @elseif(Route::currentRouteName() === 'doctors.index')
        <meta name="description" content="Узнать стоимость услуг, записаться на приём, прочитать и оставить отзывы на ветеринарного врача">
        <title>{{'Список ветеринарных врачей города '. $currentCityName }}</title>
    @elseif(Route::currentRouteName() === 'auth.login')
    <title>Авторизация</title>
    @else
        <meta name="description" content="Зверозор прочитать отзывы о домашних животных, ветеринарных клиниках, и врачах - делимся опытом, находим лучших врачей и проверенные клиники">
        <title>
    {{ filled($brandname) 
        ? $brandname . ' — сайт про домашних животных' 
        : 'Сайт про домашних животных' 
    }}
</title>


    @endif

    @vite(['resources/css/main.css', 'resources/sass/app.scss', 'resources/js/app.js'])

</head>

<style>

</style>

<body class="body_page">
<header class="site-header 
        {{ request()->is('clinics*') ? 'compact-header' : '' }}
        {{ request()->is('doctors*') ? 'compact-header' : '' }}">
    
    <div class="container py-2">

        {{-- =================== HEADER MAIN ROW =================== --}}
        <div class="header-grid">

            {{-- ==== Левый блок (город) ==== --}}

            {{-- Бургер (мобилка) --}}
<div class="d-flex d-md-none align-items-center burger-block">
<button class="btn p-1 border border-dark burger-btn"
        type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#mobileMenu"
        title="Открыть меню">

    <svg class="burger-icon"
         width="22"
         height="22"
         viewBox="0 0 24 24"
         fill="none"
         xmlns="http://www.w3.org/2000/svg">

        <line x1="3" y1="6" x2="21" y2="6" />
        <line x1="3" y1="12" x2="21" y2="12" />
        <line x1="3" y1="18" x2="21" y2="18" />
    </svg>

</button>

</div>
            <div class="city-block">
                @include('partials.city-selector')
            </div>
            {{-- ==== Логотип ==== --}}
            <div class="logo-block">
                <a href="/" class="header-logo-link" title="Перейти на главную">
                    <img class="header_logo" title="Логотип зверозор" src="{{ Storage::url('logo/logo3.png') }}" alt="{{ $brandname }}">
                </a>
            </div>

            {{-- ==== Правый блок (кнопки + профиль) ==== --}}
            <div class="right-block d-none d-md-flex align-items-center gap-3">


                {{-- Кнопки --}}
                <div class="d-none d-md-flex gap-2">
                    <button class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#addOrganizationModal">
                        <img class="add_btn" src="{{ Storage::url('icon/button/add_clinic_btn.png') }}" title="Добавить организацию" alt="Добавить организацию">
                    </button>

                    <button class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
                        <img class="add_btn" src="{{ Storage::url('icon/button/add_doctor_btn.png') }}" title="Добавить специалиста" alt="Добавить специалиста">
                    </button>
                </div>

                {{-- Профиль --}}
                <div class="d-flex align-items-center">
                    @guest
                <div class="d-flex align-items-center">
                    @guest
@guest
    <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}"
       title="Нажмите чтобы авторизоваться"
       class="login_link">

        <button type="button" class="btn_login">Войти</button>
    </a>
@endguest

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
                               title="Открыть меню"
                               data-bs-toggle="dropdown">
                                <img class="avatars_pics" title="профиль {{ Auth::user()->name }}" src="{{ asset($link) }}" alt="Аватар">
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" title="Перейти в профиль" href="{{ route('account') }}">Профиль</a>
<form action="{{ route('logout') }}" method="POST">
    @csrf

    <input type="hidden"
           name="redirect"
           value="{{ request()->getRequestUri() }}">

    <button type="submit"
            class="dropdown-item">
        Выйти
    </button>
</form>

                            </div>
                        </div>
                    @endauth
                </div>

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
                               title="Открыть меню"
                               data-bs-toggle="dropdown">
                                <img class="avatars_pics" title="профиль {{ Auth::user()->name }}" src="{{ asset($link) }}" alt="Аватар">
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" title="Перейти в профиль" href="{{ route('account') }}">Профиль</a>
<form action="{{ route('logout') }}" method="POST">
    @csrf

    <input type="hidden"
           name="redirect"
           value="{{ request()->getRequestUri() }}">

    <button type="submit"
            class="dropdown-item">
        Выйти
    </button>
</form>

                            </div>
                        </div>
                    @endauth
                </div>

            </div>

        </div>

        {{-- =================== БЛОК ПОИСКА И ОПИСАНИЯ =================== --}}
        @if(!request()->is('clinics*') && !request()->is('doctors*') && !request()->is('account*') )

            <div class="text-center mt-3">
                <h1>Сайт про домашних животных</h1>
                <p class="description_index_page">
                    На сайте вы сможете найти ветеринарные клиники, ветгостиницы, лекарства, ветеринаров и грумеров,<br>
                    а также прочесть отзывы о породах от владельцев.
                </p>
            </div>

            <div class="d-flex justify-content-center mt-3">
                <input type="search" disabled class="header-search" placeholder="Животные, породы, ветеринары, клиники">
                    <img class="btn_search" title="Найти" src="{{ Storage::url('icon/button/search.svg') }}" alt="Поиск">

            </div>

        @endif

    </div>
{{-- Мобильное бургер-меню --}}
<div class="offcanvas offcanvas-start"
     tabindex="-1"
     id="mobileMenu"
     aria-labelledby="mobileMenuLabel">

    {{-- HEADER: имя + крестик в одной строке --}}
    <div class="offcanvas-header d-flex align-items-center justify-content-between">

        @auth
        <a href="">

            <strong class="burger-profile">
                                        <div class="dropdown">
                            <a id="navbarDropdown"
                               class="profile_link dropdown-toggle d-flex align-items-center gap-2"
                               href="#"
                               role="button"
                               title="Открыть меню"
                               data-bs-toggle="dropdown">
                                <img class="avatars_pics" title="профиль {{ Auth::user()->name }}" src="{{ asset($link) }}" alt="Аватар">
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" title="Перейти в профиль" href="{{ route('account') }}">Профиль</a>
<form action="{{ route('logout') }}" method="POST">
    @csrf

    <input type="hidden"
           name="redirect"
           value="{{ request()->getRequestUri() }}">

    <button type="submit"
            class="dropdown-item">
        Выйти
    </button>
</form>

                            </div>
                        </div>
            </strong>
        </a>
        @endauth

        @guest
            <span></span>
        @endguest

        <button type="button"
                class="btn-close"
                data-bs-dismiss="offcanvas"
                aria-label="Закрыть"></button>
    </div>

    {{-- BODY --}}
    <div class="offcanvas-body d-flex flex-column gap-3">

        @guest
            <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}"
               class="btn btn-outline-primary w-100">
                Войти
            </a>
        @endguest

        <button class="btn_burger-menu"
                data-bs-toggle="modal"
                data-bs-target="#addOrganizationModal"
                data-bs-dismiss="offcanvas">
            Добавить организацию
        </button>

        <button class="btn_burger-menu"
                data-bs-toggle="modal"
                data-bs-target="#addDoctorModal"
                data-bs-dismiss="offcanvas">
            Добавить специалиста
        </button>

    </div>
</div>



</header>