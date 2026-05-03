<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:title" content="{{ $seoMeta['title'] ?? $brandname }}">
    <meta property="og:description" content="{{ $seoMeta['description'] ?? '' }}">
    <meta property="og:image" content="{{ ($seoMeta['image'] ?? 'storage/logo/og-default.png') }}">
    <meta property="og:site_name" content="Зверозор">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ request()->url() }}">
    <meta property="twitter:title" content="{{ $seoMeta['title'] ?? $brandname }}">
    <meta property="twitter:description" content="{{ $seoMeta['description'] ?? '' }}">
    <meta name="twitter:image" content="{{ ($seoMeta['image'] ?? 'storage/logo/og-default.png') }}">

    <link rel="icon" href="{{ url('favicon.ico') }}" type="image/vnd.microsoft.icon">
    <meta name="robots" content="all"/>
    <title>{{ $seoMeta['title'] ?? $brandname }}</title>
    <meta name="description" content="{{ $seoMeta['description'] ?? '' }}">

    <link rel="canonical" href="{{ request()->url() }}">

    @vite(['resources/css/main.css', 'resources/sass/app.scss', 'resources/js/app.js'])
    @stack('scripts')

<style>
    .header-search { height: 50px; border-radius: 25px 0 0 25px !important; }
    .btn-search-main { width: 60px; border-radius: 0 25px 25px 0 !important; background-color: #007bff; border: none; }
    .search-results-dropdown { position: absolute; top: 100%; left: 0; width: 100%; z-index: 1000; background: white; border: 1px solid #ddd; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .search_btn{min-width: 80px; background-color: #ffffff00; border: none; }
    .input-group{max-width: 1000px; }
    .input-group input{min-height: 50px; background-color: #fff5ee; border-radius: 15px !important;}
    
    /* Исправленный стиль для страницы объявлений */
    .header-ads-mode {
        padding-top: 0.75rem !important; /* Увеличили отступы (было 0.25) */
        padding-bottom: 0.75rem !important;
        min-height: auto !important;
    }

    .header-ads-mode .header_logo {
        max-height: 60px; /* Увеличили высоту логотипа (было 35) */
        width: auto;      /* Добавили, чтобы пропорции не искажались */
        object-fit: contain; /* Логотип будет вписываться без искажений */
    }

    /* Выравнивание элементов внутри сетки для компактного режима */
    .header-ads-mode .header-grid {
        align-items: center; 
    }
</style>
</head>

<body class="body_page">

{{-- Подключаем модалки, данные $cities придут из HeaderServiceProvider --}}
@include('account.modals.modal-add-specialist', ['cities' => $cities])
@include('account.modals.modal-add-organization', ['cities' => $cities])

<header class="site-header {{ $h->isCompact ? 'compact-header' : '' }} {{ $h->isAdsPage ? 'header-ads-mode' : '' }}">
    <div class="container-flex py-2">
        <div class="header-grid">
            {{-- Бургер (мобилка) --}}
            <div class="d-flex d-md-none align-items-center burger-block">
                <button class="btn p-1 border border-dark burger-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                    <svg class="burger-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <line x1="3" y1="6" x2="21" y2="6" stroke="black" stroke-width="2"/>
                        <line x1="3" y1="12" x2="21" y2="12" stroke="black" stroke-width="2"/>
                        <line x1="3" y1="18" x2="21" y2="18" stroke="black" stroke-width="2"/>
                    </svg>
                </button>
            </div>

            <div class="city-block">
                @include('partials.city-selector')
            </div>

            <div class="logo-block">
                <a href="/" class="header-logo-link">
                    <img class="header_logo" src="{{ Storage::url('logo/logo3.png') }}" alt="{{ $brandname }}">
                </a>
            </div>

            <div class="right-block d-none d-md-flex align-items-center gap-3">
                {{-- Кнопки добавления (скрываются на /ads) --}}
                @if(!$h->hideAddButtons)
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#addOrganizationModal">
                            <img class="add_btn" src="{{ Storage::url('icon/button/add_clinic_btn.png') }}" alt="Добавить организацию">
                        </button>
                        <button class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
                            <img class="add_btn" src="{{ Storage::url('icon/button/add_doctor_btn.png') }}" alt="Добавить специалиста">
                        </button>
                    </div>
                @endif

                @guest
                    <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="login_link">
                        <button type="button" class="btn_login">Войти</button>
                    </a>
                @endguest

                @auth
                    @php $link = "storage/avatars/default/".rand(0, 20).".png"; @endphp
                    <div class="dropdown">
                        <a class="profile_link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                            <img class="avatars_pics" src="{{ asset($link) }}" alt="Аватар">
                            {{ Auth::user()->name }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('account') }}">Профиль</a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">
                                <button type="submit" class="dropdown-item">Выйти</button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </div>

        {{-- БЛОК ПОИСКА --}}
        @if($h->showSearch)
            <div class="container mt-3">
                @if($h->showHero)
                    <div class="text-center">
                        <h1>{{ $h->title }}</h1>
                        <p class="description_index_page">{!! $h->description !!}</p>
                    </div>
                @endif

                <div class="d-flex justify-content-center">
                    <div class="input-group position-relative">
                        <input type="search" id="clinic-live-search" class="form-control header-search" placeholder="Введите название клиники..." autocomplete="off">
                        <button class="search_btn" type="button">
                            <img src="{{ Storage::url('icon/button/search.svg') }}" alt="Поиск" style="width: 48px; height: 48px;">
                        </button>
                        <div id="search-results" class="search-results-dropdown d-none"></div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Мобильное меню --}}
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
        <div class="offcanvas-header d-flex align-items-center justify-content-between">
            @auth <strong>{{ Auth::user()->name }}</strong> @endauth
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column gap-3">
            <button class="btn_burger-menu" data-bs-toggle="modal" data-bs-target="#citySelectModal">
                📍 {{ $currentCityName ?? 'Выбрать город' }}
            </button>
            @guest
                <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="btn btn-outline-primary w-100">Войти</a>
            @endguest
            
            {{-- Мобильные кнопки добавления (тоже скрываем если нужно) --}}
            @if(!$h->hideAddButtons)
                <button class="btn_burger-menu" data-bs-toggle="modal" data-bs-target="#addOrganizationModal" data-bs-dismiss="offcanvas">Добавить организацию</button>
                <button class="btn_burger-menu" data-bs-toggle="modal" data-bs-target="#addDoctorModal" data-bs-dismiss="offcanvas">Добавить специалиста</button>
            @endif
        </div>
    </div>
</header>