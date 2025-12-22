@php
    $currentCityName = auth()->user()?->city?->name
        ?? session('city_name')
        ?? 'нужно выбрать';

    $citiesIndexUrl = route('cities.index');
    $citiesSetUrl   = route('cities.set');
@endphp

@vite([
    'resources/css/main.css',
    'resources/sass/app.scss',
    'resources/js/app.js'
])

<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="city-selector"
     data-has-city="{{ session()->has('city_id') || auth()->check() ? '1' : '0' }}"
     data-cities-index-url="{{ $citiesIndexUrl }}"
     data-cities-set-url="{{ $citiesSetUrl }}"
     style="max-width:420px;">

    <div class="city-current" style="display:flex;align-items:center;gap:.4rem;">
            <strong class="city_world">Город:</strong>
        <button id="open-city-modal"
                class="select_city_btn"
                aria-haspopup="dialog">
            <span id="current-city-name">{{ $currentCityName }}</span>
        </button>
    </div>

    <div id="city-modal" style="display:none;">
        <div class="modal-content">
            <div style="display:flex;gap:.5rem;margin-bottom:.5rem;">
                <input id="city-search"
                       placeholder="Поиск города..."
                       style="flex:1;padding:.5rem;border:1px solid #ddd;border-radius:.4rem;">
                <button id="close-city-modal"
                        style="padding:.5rem .7rem;">
                    Закрыть
                </button>
            </div>

            <div id="city-list"></div>
        </div>
    </div>
</div>

<script>
(function () {
    const wrapper = document.getElementById('city-selector');
    if (!wrapper) return;

    const hasCity = wrapper.dataset.hasCity === '1';
    const citiesIndexUrl = wrapper.dataset.citiesIndexUrl;
    const citiesSetUrl   = wrapper.dataset.citiesSetUrl;

    const openBtn  = document.getElementById('open-city-modal');
    const closeBtn = document.getElementById('close-city-modal');
    const modal    = document.getElementById('city-modal');
    const list     = document.getElementById('city-list');
    const search   = document.getElementById('city-search');

    let allCities   = [];
    let largeCities = [];

    document.addEventListener('DOMContentLoaded', () => {
        if (!hasCity) {
            modal.style.display = 'block';
            loadAllCities();
        }
    });

    openBtn.addEventListener('click', () => {
        search.value = '';
        modal.style.display = 'block';

        if (allCities.length) {
            renderCities(largeCities);
        } else {
            loadAllCities();
        }
    });

    closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    modal.addEventListener('click', e => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    async function loadAllCities() {
        list.innerHTML = '<div style="padding:1rem;text-align:center;">Загрузка…</div>';

        try {
            const res = await fetch(citiesIndexUrl, {
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) throw new Error(res.status);

            allCities = await res.json();

            // ✅ large_city приходит как 1 / 0
            largeCities = allCities.filter(c => Number(c.large_city) === 1);

            renderCities(largeCities);
        } catch {
            list.innerHTML =
                '<div style="padding:1rem;text-align:center;color:red;">Ошибка загрузки</div>';
        }
    }

    function renderCities(cities) {
        if (!cities.length) {
            list.innerHTML =
                '<div style="padding:1rem;text-align:center;color:#777;">Ничего не найдено</div>';
            return;
        }

        list.innerHTML = cities.map(c => `
            <button class="city-item" data-id="${c.id}">
                ${c.name}
            </button>
        `).join('');

        list.querySelectorAll('.city-item').forEach(btn => {
            btn.addEventListener('click', () => {
                setCity(btn.dataset.id);
            });
        });
    }

    async function setCity(cityId) {
        try {
            const token = document
                .querySelector('meta[name="csrf-token"]')
                .content;

            const res = await fetch(citiesSetUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ city_id: cityId })
            });

            if (!res.ok) throw new Error();

            modal.style.display = 'none';
            window.location.reload();
        } catch {
            alert('Не удалось установить город');
        }
    }

    search.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();

        if (!q) {
            renderCities(largeCities);
            return;
        }

        renderCities(
            allCities.filter(c =>
                c.name.toLowerCase().includes(q)
            )
        );
    });
})();
</script>
