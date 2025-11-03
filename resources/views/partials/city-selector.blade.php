@php
    $currentCityName = auth()->user()?->city?->name ?? session('city_name', ' нужно выбрать');
    $citiesIndexUrl = route('cities.index');
    $citiesSetUrl   = route('cities.set');
@endphp

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
.select_city_btn {
    color: #0066ff;
    background-color: transparent;
    border: none;
    margin-left: 2%;
    font-weight: 700;
    cursor: pointer;
}

#city-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 9999;
}

#city-modal .modal-content {
    background: #fff;
    max-width: 600px;
    margin: 6vh auto;
    padding: 1rem;
    border-radius: .6rem;
    max-height: 80vh;
    overflow: auto;
}

#city-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: .4rem;
}

.city-item {
    text-align: left;
    padding: .6rem;
    border: 1px solid #eee;
    border-radius: .4rem;
    background: #fafafa;
    cursor: pointer;
}

.city-item:hover {
    background: #f0f8ff;
}
</style>

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
                <input id="city-search" placeholder="Поиск города..."
                       style="flex:1;padding:.5rem;border:1px solid #ddd;border-radius:.4rem;">
                <button id="close-city-modal" style="padding:.5rem .7rem;">Закрыть</button>
            </div>

            <div id="city-list"></div>
        </div>
    </div>
</div>

<script>
(function(){
    const wrapper = document.getElementById('city-selector');
    const citiesIndexUrl = wrapper.dataset.citiesIndexUrl;
    const citiesSetUrl   = wrapper.dataset.citiesSetUrl;

    const openBtn = document.getElementById('open-city-modal');
    const closeBtn = document.getElementById('close-city-modal');
    const modal = document.getElementById('city-modal');
    const list = document.getElementById('city-list');
    const search = document.getElementById('city-search');
    const currentName = document.getElementById('current-city-name');

    let allCities = [];

    openBtn.addEventListener('click', () => {
        // 🔹 Очистка поля поиска и сброс фильтра
        search.value = '';
        modal.style.display = 'block';
        if (allCities.length) {
            renderCities(allCities);
        } else {
            loadAllCities();
        }
    });

    closeBtn.addEventListener('click', () => modal.style.display = 'none');
    modal.addEventListener('click', (e) => { if (e.target === modal) modal.style.display = 'none'; });

    async function loadAllCities() {
        list.innerHTML = '<div style="grid-column:1/-1;padding:1rem;text-align:center;">Загрузка…</div>';
        try {
            const res = await fetch(citiesIndexUrl, { headers: {'Accept':'application/json'} });
            if (!res.ok) throw new Error('Ошибка загрузки: ' + res.status);
            allCities = await res.json();

            if (!Array.isArray(allCities) || !allCities.length) {
                list.innerHTML = '<div style="grid-column:1/-1;padding:1rem;text-align:center;">Нет данных</div>';
                return;
            }

            renderCities(allCities);
        } catch (err) {
            console.error(err);
            list.innerHTML = '<div style="grid-column:1/-1;padding:1rem;text-align:center;color:red;">Ошибка загрузки</div>';
        }
    }

    function renderCities(cities) {
        list.innerHTML = cities.map(c => `
            <button class="city-item" data-id="${c.id}">${c.name}</button>
        `).join('');

        document.querySelectorAll('.city-item').forEach(btn => {
            btn.addEventListener('click', async () => {
                const cityId = btn.dataset.id;
                await setCity(cityId);
            });
        });
    }

    async function setCity(cityId) {
        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch(citiesSetUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ city_id: cityId })
            });

            if (!res.ok) throw new Error('Ошибка сети');
            const json = await res.json();

            currentName.textContent = json.city.name;
            modal.style.display = 'none';
        } catch (err) {
            alert('Не удалось установить город. Попробуйте ещё раз.');
            console.error(err);
        }
    }

    // 🔍 Поиск (фильтрация локально)
    search.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();
        if (!q) {
            renderCities(allCities);
            return;
        }
        const filtered = allCities.filter(c => c.name.toLowerCase().includes(q));
        renderCities(filtered);
    });
})();
$.ajax({
    url: '/cities/list',
    type: 'GET',
    success: function (data) {
        const $select = $('#city-select');
        $select.empty();

        data.forEach(city => {
            const option = new Option(city.name, city.id, false, false);
            $select.append(option);
        });

        // Добавляем пункт "Моего города нет в списке"
        $select.append(new Option('Моего города нет в списке', 'add_new_city'));
    }
});

</script>

