
@php
    $currentCityName = auth()->user()?->city?->name ?? session('city_name', 'нужно выбрать');
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
     data-cities-index-url="{{ $citiesIndexUrl }}"
     data-cities-set-url="{{ $citiesSetUrl }}"
     style="max-width:420px;">
    <div class="city-current" style="display:flex;align-items:center;">
        <div><strong>Город:</strong></div>
        <button id="open-city-modal" class="select_city_btn" aria-haspopup="dialog">
            <span id="current-city-name">{{ $currentCityName }}</span>
        </button>
    </div>

    <div id="city-modal">
        <div class="modal-content">
            <div style="display:flex;gap:.5rem;margin-bottom:.5rem;">
                <input id="city-search"
                       placeholder="Поиск города..."
                       style="flex:1;padding:.5rem;border:1px solid #ddd;border-radius:.4rem;">
                <button id="close-city-modal" style="padding:.5rem .7rem;">Закрыть</button>
            </div>

            <div id="city-list"></div>
        </div>
    </div>
</div>

<script>
(function () {
    const wrapper = document.getElementById('city-selector');
    const citiesIndexUrl = wrapper.dataset.citiesIndexUrl;
    const citiesSetUrl   = wrapper.dataset.citiesSetUrl;

    const openBtn = document.getElementById('open-city-modal');
    const closeBtn = document.getElementById('close-city-modal');
    const modal = document.getElementById('city-modal');
    const list = document.getElementById('city-list');
    const search = document.getElementById('city-search');

    let allCities = [];

    openBtn.addEventListener('click', () => {
        search.value = '';
        modal.style.display = 'block';

        if (allCities.length) {
            renderCities(allCities);
        } else {
            loadAllCities();
        }
    });

    closeBtn.addEventListener('click', () => modal.style.display = 'none');
    modal.addEventListener('click', e => {
        if (e.target === modal) modal.style.display = 'none';
    });

    async function loadAllCities() {
        list.innerHTML = '<div style="grid-column:1/-1;padding:1rem;text-align:center;">Загрузка…</div>';
        try {
            const res = await fetch(citiesIndexUrl, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error(res.status);
            allCities = await res.json();
            renderCities(allCities);
        } catch {
            list.innerHTML = '<div style="grid-column:1/-1;padding:1rem;text-align:center;color:red;">Ошибка загрузки</div>';
        }
    }

    function renderCities(cities) {
        list.innerHTML = cities.map(c => `
            <button class="city-item" data-id="${c.id}" data-name="${c.name}">
                ${c.name}
            </button>
        `).join('');

        document.querySelectorAll('.city-item').forEach(btn => {
            btn.addEventListener('click', () => {
                setCity(btn.dataset.id);
            });
        });
    }

    async function setCity(cityId) {
        try {
            const token = document.querySelector('meta[name="csrf-token"]').content;

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

            // ✅ Обычная перезагрузка страницы
            window.location.reload();

        } catch {
            alert('Не удалось установить город');
        }
    }

    // 🔍 Поиск
    search.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();
        if (!q) return renderCities(allCities);
        renderCities(allCities.filter(c => c.name.toLowerCase().includes(q)));
    });
})();
</script>
