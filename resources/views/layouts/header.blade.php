@include('account.modals.modal-add-specialist', ['cities' => $cities])
@include('account.modals.modal-add-organization', ['cities' => $cities])

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ url('favicon.ico') }}" type="image/vnd.microsoft.icon">
<meta name="robots" content="all"/>

<!-- ТУТ НАДО БУДЕТ МАРШРУТЫ СДЕЛАТЬ НОРМАЛЬНО И СЕОшку к ним -->
@if(request()->is('/'))
    <title>Зверозор — отзывы о ветеринарных клиниках и врачах города</title>
    <meta name="description" content="Честный рейтинг ветеринарных клиник и врачей. Отзывы владельцев животных, находите проверенных специалистов и делитесь опытом. Все услуги для ваших питомцев.">

@elseif(Route::currentRouteName() === 'specialists.show')
    {{-- СЕО ДЛЯ СТРАНИЦЫ СПЕЦИАЛИСТА --}}
    @php
        $currentCityName = $doctor->city->name ?? $currentCityName;
        $workPlace = $doctor->organization->name ?? '';
    @endphp
    <link rel="canonical" href="{{ route('specialists.show', $doctor->slug) }}">
    <title>{{ $doctor->name }}{{ $workPlace ? ' — ' . $workPlace : '' }} {{ $doctor->specialization }} | {{ $currentCityName }}</title>
    <meta name="description" content="{{ $doctor->specialization }} {{ $doctor->name }}. {{ $workPlace ? 'Место работы: ' . $workPlace . '.' : '' }} Город {{ $currentCityName }}. Прочитать отзывы от реальных клиентов, узнать стоимость услуг.">

@elseif(Route::currentRouteName() === 'specialists.index')
    {{-- СЕО ДЛЯ СПИСКА СПЕЦИАЛИСТОВ --}}
    <title>Специалисты по работе с животными в г. {{ $currentCityName }}</title>
    <meta name="description" content="Полный список специалистов: грумеры, кинологи, ветеринары и другие эксперты в городе {{ $currentCityName }}. Рейтинг на основе отзывов, поиск по услугам.">

@elseif(Route::currentRouteName() === 'clinics.show')
    {{-- (Ваш существующий код для клиник) --}}
    @php $currentCityName = $pageCityName ?? $currentCityName; @endphp
    <title>{{ $clinic->name ? $clinic->name . ' ' . $currentCityName . ' — адрес, отзывы' : 'Сайт про животных' }}</title>
    <meta name="description" content="{{ $clinic->name ? 'Ветеринарная клиника ' . $clinic->name . ' в городе ' . $currentCityName . ', услуги, график работы, отзывы' : 'Сайт про животных' }}">

@elseif(Route::currentRouteName() === 'clinics.index')
    <title>{{ 'Все ветеринарные клиники города ' . $currentCityName }}</title>
    <meta name="description" content="Найти ветеринарную клинику в городе {{ $currentCityName }}, узнать рейтинг, прочитать отзывы и контакты.">

@elseif(Route::currentRouteName() === 'doctors.show')
    {{-- (Ваш существующий код для врачей) --}}
    @php
        $currentCityName = $pageCityName ?? $doctor->city?->name ?? $currentCityName;
        $seoClinic = $doctor->clinic?->name;
    @endphp
    <link rel="canonical" href="{{ route('doctors.show', $doctor->slug) }}">
    <title>{{ $doctor->name }}{{ $seoClinic ? ' — ' . $seoClinic : '' }} ветеринар {{ $currentCityName }}</title>
    <meta name="description" content="Ветеринарный врач {{ $doctor->name }} {{ $doctor->specialization ? ' — ' . mb_strtolower($doctor->specialization) : '' }}{{ $seoClinic ? ', клиника ' . $seoClinic : '' }} в г. {{ $currentCityName }}.">

@elseif(Route::currentRouteName() === 'doctors.index')
    <title>{{ 'Список ветеринарных врачей города ' . $currentCityName }}</title>
    <meta name="description" content="Узнать стоимость услуг, записаться на приём, прочитать отзывы на ветеринарного врача в г. {{ $currentCityName }}">

@elseif(Route::currentRouteName() === 'auth.login')
    <title>Авторизация | Зверозор</title>
    <meta name="description" content="Войдите в личный кабинет Зверозор, чтобы оставить отзыв или добавить специалиста.">

@else
    <meta name="description" content="Зверозор — делимся опытом, находим лучших врачей и проверенные клиники для ваших питомцев.">
    <title>{{ filled($brandname) ? $brandname . ' — сайт про домашних животных' : 'Сайт отзывов о домашних животных' }}</title>
@endif


    @vite(['resources/css/main.css', 'resources/sass/app.scss', 'resources/js/app.js'])

</head>

<body class="body_page">
<header class="site-header 
        {{ request()->is('clinics*') ? 'compact-header' : '' }}
        {{ request()->is('doctors*') ? 'compact-header' : '' }}">
    
    <div class="container-flex py-2">

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
                    </button><br>

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
       {{-- Показываем поиск если: это НЕ врачи, НЕ аккаунт, НЕ инфо-страницы И (это НЕ клиники ИЛИ это страница конкретной клиники) --}}
@if(!request()->is( 'account*', 'legal/*') || Route::currentRouteName() === 'clinics.show')

<div class="text-center mt-3">
    {{-- Убираем H1 и описание для страницы клиники, чтобы не дублировать SEO-теги --}}
@php
    // Проверяем, нужно ли показывать поиск
    $showSearch = !request()->is('account*', 'legal/*') || Route::currentRouteName() === 'clinics.show';
    // Проверяем, нужно ли показывать заголовок и описание (только на главной)
    $showHero = request()->is('/'); 
@endphp

@if($showSearch)
    <div class="text-center mt-3">
        @if($showHero)
            <h1>Сайт про домашних животных</h1>
            <p class="description_index_page">
                На сайте вы сможете найти ветеринарные клиники, ветгостиницы, лекарства, ветеринаров и грумеров,<br>
                а также прочесть отзывы о породах от владельцев.
            </p>
        @endif
    </div>
@endif

    </div>

    <div class="search-container mt-3 position-relative">
        <div class="d-flex justify-content-center">
            {{-- Убираем disabled и добавляем id для JS --}}
            <input type="search" id="clinic-live-search" class="header-search" placeholder="Введите название клиники..." autocomplete="off">
            <img class="btn_search" title="Найти" src="{{ Storage::url('icon/button/search.svg') }}" alt="Поиск">
        </div>
        {{-- Контейнер для результатов --}}
        <div id="search-results" class="search-results-dropdown d-none"></div>
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

{{-- Выбор города (мобилка) --}}
<button class="btn_burger-menu"
        data-bs-toggle="modal"
        data-bs-target="#citySelectModal">
    📍 {{ $currentCityName ?? 'Выбрать город' }}
</button>


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
