<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta property="og:type"        content="website">
    <meta property="og:url"         content="{{ request()->url() }}">
    <meta property="og:title"       content="{{ $seoMeta['title'] ?? $brandname }}">
    <meta property="og:description" content="{{ $seoMeta['description'] ?? '' }}">
    <meta property="og:image"       content="{{ $seoMeta['image'] ?? asset('storage/logo/og-default.png') }}">
    <meta property="og:site_name"   content="Зверозор">
    <meta property="og:image:width"  content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:url"         content="{{ request()->url() }}">
    <meta name="twitter:title"       content="{{ $seoMeta['title'] ?? $brandname }}">
    <meta name="twitter:description" content="{{ $seoMeta['description'] ?? '' }}">
    <meta name="twitter:image"       content="{{ $seoMeta['image'] ?? asset('storage/logo/og-default.png') }}">

    <link rel="icon" href="{{ url('favicon.ico') }}" type="image/vnd.microsoft.icon">
    <meta name="robots" content="all">
    <title>{{ $seoMeta['title'] ?? $brandname }}</title>
    <meta name="description" content="{{ $seoMeta['description'] ?? '' }}">
    <link rel="canonical" href="{{ request()->url() }}">

@vite(['resources/css/main.css', 'resources/css/mobile.css', 'resources/sass/app.scss', 'resources/js/app.js'])
    @stack('scripts')

    @if(!isset($h))
        @php
            $h = (object)[
                'showSearch'     => true,
                'showHero'       => false,
                'isCompact'      => false,
                'isAdsPage'      => false,
                'hideAddButtons' => false,
            ];
        @endphp
    @endif

    @auth
        @php
            // 1. Инициализируем пустой массив для всех подтвержденных кабинетов
            $userCabinets = [];
            $hasPendingRequest = false;

            $clinicOwner = \App\Models\ClinicOwner::where('user_id', auth()->id())->first();
            $orgOwner    = \App\Models\OrganizationOwner::where('user_id', auth()->id())->first();
            $doctorOwner = \App\Models\DoctorOwner::where('user_id', auth()->id())->first();
            $specOwner   = \App\Models\SpecialistOwner::where('user_id', auth()->id())->first();

            // 2. Наполняем массив каждым подтвержденным кабинетом (теперь они собираются вместе!)
            if ($clinicOwner?->is_confirmed) {
                $userCabinets[] = [
                    'url'   => route('owner.clinic', $clinicOwner->clinic_id),
                    'label' => 'Кабинет клиники',
                    'icon'  => '🏥'
                ];
            } 
            if ($orgOwner?->is_confirmed) {
                $userCabinets[] = [
                    'url'   => route('owner.organization', $orgOwner->organization_id),
                    'label' => 'Кабинет организации',
                    'icon'  => '🏢'
                ];
            } 
            if ($doctorOwner?->is_confirmed) {
                $userCabinets[] = [
                    'url'   => route('owner.doctor', $doctorOwner->doctor_id),
                    'label' => 'Кабинет врача',
                    'icon'  => '👨‍⚕️'
                ];
            } 
            if ($specOwner?->is_confirmed) {
                $userCabinets[] = [
                    'url'   => route('owner.specialist', $specOwner->specialist_id),
                    'label' => 'Кабинет специалиста',
                    'icon'  => '🩺'
                ];
            }

            // 3. Проверяем, есть ли неподтвержденные заявки, ТОЛЬКО если ни один кабинет еще не подтвержден
            $hasPendingRequest = empty($userCabinets) && (
                ($clinicOwner && !$clinicOwner->is_confirmed)  ||
                ($orgOwner    && !$orgOwner->is_confirmed)     ||
                ($doctorOwner && !$doctorOwner->is_confirmed)  ||
                ($specOwner   && !$specOwner->is_confirmed)
            );
        @endphp
    @endauth

    <style>
        .header-search { height: 50px; border-radius: 25px 0 0 25px !important; }
        .search_btn { min-width: 80px; background-color: transparent; border: none; }
        .input-group { max-width: 1000px; }
        .input-group input { min-height: 50px; background-color: #fff5ee; border-radius: 15px !important; }
        .search-results-dropdown { position: absolute; top: 100%; left: 0; width: 100%; z-index: 1000; background: white; border: 1px solid #ddd; box-shadow: 0 4px 10px rgba(0,0,0,.1); }

        .header-ads-mode { padding-top: .75rem !important; padding-bottom: .75rem !important; min-height: auto !important; }
        .header-ads-mode .header_logo { max-height: 60px; width: auto; object-fit: contain; }
        .header-ads-mode .header-grid { align-items: center; }

        #mobileMenu { width: 300px !important; }
        .mobile-nav-section { border-bottom: 1px solid #f0f0f0; padding: 12px 0; }
        .mobile-nav-section:last-child { border-bottom: none; }
        .mobile-nav-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #aaa; padding: 0 4px 6px; }
        .mobile-nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 10px; border: none; background: none;
            width: 100%; text-align: left; font-size: 15px; color: #222;
            text-decoration: none; cursor: pointer; transition: background .15s;
        }
        .mobile-nav-link:hover { background: #f5f5f5; color: #222; }
        .mobile-nav-link .nav-icon { font-size: 18px; width: 24px; text-align: center; flex-shrink: 0; }
        .mobile-nav-link.style-primary { background: #e8f0fe; color: #1a56db; font-weight: 600; }
        .mobile-nav-link.style-owner   { background: #f0fdf4; color: #15803d; font-weight: 600; }
        .mobile-nav-link.style-pending { color: #999; }
        .mobile-nav-link.style-danger  { background: #fef2f2; color: #dc2626; }

        #mobile-city-panel { display: none; flex-direction: column; gap: 6px; padding-top: 8px; }
        #mobile-city-panel.open { display: flex; }
        #mobile-city-search { padding: 8px 12px; border: 1px solid #ddd; border-radius: 10px; font-size: 14px; width: 100%; outline: none; }
        #mobile-city-search:focus { border-color: #007bff; }
        #mobile-city-list { max-height: 240px; overflow-y: auto; display: flex; flex-direction: column; gap: 2px; }
        .mobile-city-item { padding: 9px 12px; border-radius: 8px; border: none; background: none; text-align: left; font-size: 14px; cursor: pointer; color: #333; }
        .mobile-city-item:hover { background: #f0f5ff; color: #1a56db; }
    </style>
</head>

<body class="body_page">

@include('account.modals.modal-add-specialist',   ['cities' => $cities])
@include('account.modals.modal-add-organization', ['cities' => $cities])

<header class="site-header {{ $h->isCompact ? 'compact-header' : '' }} {{ $h->isAdsPage ? 'header-ads-mode' : '' }}">
    <div class="container-flex py-2">
        <div class="header-grid">

            <div class="d-flex d-md-none align-items-center burger-block">
                <button class="btn p-1 border border-dark burger-btn" type="button"
                        data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
                    <svg class="burger-icon" width="22" height="22" viewBox="0 0 24 24" fill="none">
                        <line x1="3"  y1="6"  x2="21" y2="6"  stroke="black" stroke-width="2"/>
                        <line x1="3"  y1="12" x2="21" y2="12" stroke="black" stroke-width="2"/>
                        <line x1="3"  y1="18" x2="21" y2="18" stroke="black" stroke-width="2"/>
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
                    @php $avatarLink = 'storage/avatars/default/' . (auth()->id() % 20) . '.png'; @endphp
                    <div class="dropdown">
                        <a class="profile_link dropdown-toggle d-flex align-items-center gap-2"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img class="avatars_pics" src="{{ asset($avatarLink) }}" alt="Аватар">
                            {{ Auth::user()->name }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-2">
                            <a class="dropdown-item" href="{{ route('account') }}">👤 Профиль</a>

                            {{-- Если есть хотя бы один подтвержденный кабинет --}}
                            @if(!empty($userCabinets))
                                <div class="dropdown-divider"></div>
                                @foreach($userCabinets as $cabinet)
                                    <a class="dropdown-item fw-semibold text-success" href="{{ $cabinet['url'] }}">
                                        {{ $cabinet['icon'] }} {{ $cabinet['label'] }}
                                    </a>
                                @endforeach
                                <div class="dropdown-divider"></div>
                            
                            {{-- Если подтвержденных нет, но заявка на модерации --}}
                            @elseif($hasPendingRequest)
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-muted" href="{{ route('owner.index') }}">
                                    ⏳ Кабинет (на проверке)
                                </a>
                                <div class="dropdown-divider"></div>
                            @endif

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">
                                <button type="submit" class="dropdown-item text-danger">🚪 Выйти</button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </div>


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
                        <input type="search" id="clinic-live-search" class="form-control header-search"
                               placeholder="Введите название клиники..." autocomplete="off">
                        <button class="search_btn" type="button">
                            <img src="{{ Storage::url('icon/button/search.svg') }}" alt="Поиск" style="width:48px;height:48px;">
                        </button>
                        <div id="search-results" class="search-results-dropdown d-none"></div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">

        <div class="offcanvas-header px-4 py-3 border-bottom">
            @auth
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('storage/avatars/default/' . (auth()->id() % 20) . '.png') }}"
                         alt="Аватар" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                    <div>
                        <div class="fw-bold" style="font-size:15px;">{{ Auth::user()->name }}</div>
                        <div class="text-muted" style="font-size:12px;">{{ Auth::user()->email }}</div>
                    </div>
                </div>
            @else
                <strong id="mobileMenuLabel" style="font-size:16px;">Меню</strong>
            @endauth
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Закрыть"></button>
        </div>

        <div class="offcanvas-body px-3 py-2" style="overflow-y:auto;">

            <div class="mobile-nav-section">
                <div class="mobile-nav-label">📍 Город</div>
                <button class="mobile-nav-link" id="mobile-city-toggle" type="button">
                    <span class="nav-icon">🏙️</span>
                    <span id="mobile-current-city">{{ $currentCityName ?? 'Выбрать город' }}</span>
                    <span id="mobile-city-arrow" style="margin-left:auto;font-size:12px;color:#aaa;">▼</span>
                </button>
                <div id="mobile-city-panel">
                    <input type="text" id="mobile-city-search" placeholder="Поиск города...">
                    <div id="mobile-city-list">
                        <div style="padding:8px;text-align:center;color:#aaa;font-size:13px;">Загрузка…</div>
                    </div>
                </div>
            </div>

            <div class="mobile-nav-section">
                <div class="mobile-nav-label">Навигация</div>
                <a href="/" class="mobile-nav-link" >
                    <span class="nav-icon">🏠</span> Главная
                </a>
                <a href="{{ route('organizations.index') }}" class="mobile-nav-link" >
                    <span class="nav-icon">🏥</span> Организации
                </a>
                <a href="{{ route('legal/news') }}" class="mobile-nav-link" >
                    <span class="nav-icon">📰</span> Новости
                </a>
                <a href="{{ route('legal/glossary') }}" class="mobile-nav-link" >
                    <span class="nav-icon">📖</span> Глоссарий
                </a>
                <a href="{{ route('legal/faq') }}" class="mobile-nav-link" >
                    <span class="nav-icon">❓</span> FAQ
                </a>
            </div>

            @if(!$h->hideAddButtons)
                <div class="mobile-nav-section">
                    <div class="mobile-nav-label">Добавить</div>
                    <button class="mobile-nav-link" id="mobile-btn-add-org" type="button">
                        <span class="nav-icon">🏢</span> Добавить организацию
                    </button>
                    <button class="mobile-nav-link" id="mobile-btn-add-doctor" type="button">
                        <span class="nav-icon">👨‍⚕️</span> Добавить специалиста
                    </button>
                </div>
            @endif

            <div class="mobile-nav-section">
                @guest
                    <div class="mobile-nav-label">Аккаунт</div>
                    <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}"
                       class="mobile-nav-link style-primary" >
                        <span class="nav-icon">🔑</span> Войти
                    </a>
                    <a href="{{ route('register') }}" class="mobile-nav-link" >
                        <span class="nav-icon">✏️</span> Зарегистрироваться
                    </a>
                @endguest

                @auth
                    <div class="mobile-nav-label">Личный кабинет</div>
                    <a href="{{ route('account') }}" class="mobile-nav-link" >
                        <span class="nav-icon">👤</span> Мой профиль
                    </a>
                    <a href="{{ route('account') }}#pets" class="mobile-nav-link" >
                        <span class="nav-icon">🐾</span> Мои питомцы
                    </a>
                    <a href="{{ route('account') }}#reviews" class="mobile-nav-link" >
                        <span class="nav-icon">⭐</span> Мои отзывы
                    </a>

@if(isset($userCabinets) && count($userCabinets) > 0)
    @foreach($userCabinets as $cabinet)
        <hr style="margin:6px 0;opacity:.15;">
        <a href="{{ $cabinet['url'] }}" class="mobile-nav-link style-owner" >
            <span class="nav-icon">{{ $cabinet['icon'] }}</span> {{ $cabinet['label'] }}
        </a>
    @endforeach
@elseif(!empty($hasPendingRequest))
    <hr style="margin:6px 0;opacity:.15;">
    <a href="{{ route('owner.index') }}" class="mobile-nav-link style-pending" >
        <span class="nav-icon">⏳</span> Кабинет (на проверке)
    </a>
@endif
                    <hr style="margin:6px 0;opacity:.15;">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">
                        <button type="submit" class="mobile-nav-link style-danger">
                            <span class="nav-icon">🚪</span> Выйти
                        </button>
                    </form>
                @endauth
            </div>

        </div>
    </div>

</header>

<script>
(function () {
    'use strict';

    const CITIES_INDEX_URL = "{{ route('cities.index') }}";
    const CITIES_SET_URL   = "{{ route('cities.set') }}";

    let allCities    = [];
    let largeCities  = [];
    let citiesLoaded = false;

    // ── Безопасное получение/создание Bootstrap-компонента ──────
    function getBsOffcanvas(el) {
        // Используем getOrCreateInstance — работает и до и после открытия
        return window.bootstrap?.Offcanvas?.getOrCreateInstance(el) ?? null;
    }

    function getBsModal(el) {
        return window.bootstrap?.Modal?.getOrCreateInstance(el) ?? null;
    }

    // ── Закрыть offcanvas → подождать → открыть модалку ─────────
    function openModalAfterOffcanvas(modalId) {
        const offcanvasEl = document.getElementById('mobileMenu');
        if (!offcanvasEl) return;

        const instance = getBsOffcanvas(offcanvasEl);
        if (instance) {
            instance.hide();
        }

        // Ждём события скрытия — надёжнее чем setTimeout
        const onHidden = function () {
            offcanvasEl.removeEventListener('hidden.bs.offcanvas', onHidden);
            const modalEl = document.getElementById(modalId);
            if (modalEl) {
                getBsModal(modalEl)?.show();
            }
        };
        offcanvasEl.addEventListener('hidden.bs.offcanvas', onHidden);

        // Страховочный таймаут если событие не придёт
        setTimeout(function () {
            offcanvasEl.removeEventListener('hidden.bs.offcanvas', onHidden);
            const modalEl = document.getElementById(modalId);
            if (modalEl && !modalEl.classList.contains('show')) {
                getBsModal(modalEl)?.show();
            }
        }, 500);
    }

    // ── Инициализация после полной загрузки страницы ────────────
    function init() {
        // Кнопки добавления
        const btnOrg    = document.getElementById('mobile-btn-add-org');
        const btnDoctor = document.getElementById('mobile-btn-add-doctor');

        if (btnOrg) {
            btnOrg.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openModalAfterOffcanvas('addOrganizationModal');
            });
        }

        if (btnDoctor) {
            btnDoctor.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openModalAfterOffcanvas('addDoctorModal');
            });
        }

        // ── Выбор города ─────────────────────────────────────────
        const toggle    = document.getElementById('mobile-city-toggle');
        const panel     = document.getElementById('mobile-city-panel');
        const arrow     = document.getElementById('mobile-city-arrow');
        const cityList  = document.getElementById('mobile-city-list');
        const cityInput = document.getElementById('mobile-city-search');
        const cityLabel = document.getElementById('mobile-current-city');

        if (!toggle || !panel) return;

        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const isOpen = panel.classList.toggle('open');
            if (arrow) arrow.textContent = isOpen ? '▲' : '▼';
            if (isOpen && !citiesLoaded) loadCities();
        });

        if (cityInput) {
            cityInput.addEventListener('input', function () {
                const q = this.value.trim().toLowerCase();
                renderCities(q
                    ? allCities.filter(c => c.name.toLowerCase().includes(q))
                    : largeCities
                );
            });
        }

        async function loadCities() {
            if (!cityList) return;
            cityList.innerHTML = '<div style="padding:8px;text-align:center;color:#aaa;font-size:13px;">Загрузка…</div>';
            try {
                const res  = await fetch(CITIES_INDEX_URL, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                allCities   = Array.isArray(data) ? data : (data.data ?? []);
                largeCities = allCities.filter(c => Number(c.large_city) === 1);
                citiesLoaded = true;
                renderCities(largeCities.length ? largeCities : allCities);
            } catch (err) {
                console.error('Cities load error:', err);
                if (cityList) cityList.innerHTML = '<div style="padding:8px;text-align:center;color:red;font-size:13px;">Ошибка загрузки</div>';
            }
        }

        function renderCities(cities) {
            if (!cityList) return;
            if (!cities.length) {
                cityList.innerHTML = '<div style="padding:8px;text-align:center;color:#aaa;font-size:13px;">Ничего не найдено</div>';
                return;
            }
            cityList.innerHTML = cities.map(c =>
                `<button class="mobile-city-item" type="button" data-id="${c.id}">${c.name}</button>`
            ).join('');
            cityList.querySelectorAll('.mobile-city-item').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setCity(this.dataset.id, this.textContent.trim());
                });
            });
        }

        async function setCity(cityId, cityName) {
            const tokenEl = document.querySelector('meta[name="csrf-token"]');
            const token   = tokenEl ? tokenEl.content : '';
            try {
                const res = await fetch(CITIES_SET_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ city_id: cityId }),
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                if (cityLabel) cityLabel.textContent = cityName;
                const desktopLabel = document.getElementById('current-city-name');
                if (desktopLabel) desktopLabel.textContent = cityName;
                if (panel) panel.classList.remove('open');
                if (arrow) arrow.textContent = '▼';
                window.location.reload();
            } catch (err) {
                console.error('Set city error:', err);
                alert('Не удалось установить город. Попробуйте ещё раз.');
            }
        }
    }

    // Запускаем после полной загрузки — гарантирует что Bootstrap инициализирован
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // DOM уже готов (скрипт в конце body)
        init();
    }

})();
</script>

</body>
</html>