@include('account.modals.modal-add-doctor', ['cities' => $cities])


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ url('favicon.ico') }}">

    @if(Route::currentRouteName() === 'clinics.show')
        <title>{{ $clinic->name ? $clinic->name . ' — контакты и отзывы о клинике в городе ' . $clinic->city : 'Сайт про домашних животных' }}</title>

    @elseif(Route::currentRouteName() === 'doctors.show')
        <title>{{ $doctor->name ? $doctor->name . ' — ветеринар в городе ' . $doctor->city : 'Сайт про домашних животных' }}</title>

    @else
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
        <div class="d-flex align-items-center justify-content-between">

            {{-- ==== Левый блок (город) ==== --}}
            <div class="flex-shrink-0">
                @include('partials.city-selector')
            </div>

            {{-- ==== Логотип ==== --}}
            <div class="flex-grow-1 text-center">
                <a href="/" class="header-logo-link d-inline-block">
                    <img class="header_logo" src="{{ Storage::url('logo/logo3.png') }}" alt="{{ $brandname }}">
                </a>
            </div>

            {{-- ==== Кнопки ==== --}}
            <div class="d-none d-md-flex gap-2 flex-shrink-0 me-3">
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
                    Специалист+
                </button>

                <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#addClinicModal">
                    Клиника+
                </button>
            </div>

            {{-- ==== Профиль ==== --}}
            <div class="flex-shrink-0 d-flex align-items-center">
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
                <a href="#" class="btn_search_link ms-2">
                    <img class="btn_search" src="{{ Storage::url('icon/button/search.svg') }}" alt="Поиск">
                </a>
            </div>

        @endif

    </div>

</header>

</body>
</html>
