<!-- city-selector.blade.php -->
@php
    // $currentCity доступен из view composer
@endphp
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
.select_city_btn{
color: #0066ffff;
background-color: #ffffff01;
/* opacity: 0; */
border: none;
margin-left: 2%;
font-weight: 700;
}
</style>

<div id="city-selector" style="max-width:420px;">
    <div class="city-current" style="display:flex;align-items:center;">
        <div>
            <strong>Город:</strong>
        </div>
        <button id="open-city-modal" class="select_city_btn" aria-haspopup="dialog">
            <span id="current-city-name">{{ $currentCity ? $currentCity->name : '—' }}</span>
        </button>
    </div>

    <!-- Modal / Dropdown -->
    <div id="city-modal" style="display:none;position:fixed;left:0;right:0;top:0;bottom:0;background:rgba(0,0,0,0.4);z-index:9999;">
        <div style="background:#fff;max-width:600px;margin:6vh auto;padding:1rem;border-radius:.6rem;max-height:80vh;overflow:auto;">
            <div style="display:flex;gap:.5rem;margin-bottom:.5rem;">
                <input id="city-search" placeholder="Поиск города..." style="flex:1;padding:.5rem;border:1px solid #ddd;border-radius:.4rem;">
                <button id="close-city-modal" style="padding:.5rem  .7rem;">Закрыть</button>
            </div>

            <div id="city-list" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:.4rem;">
                <!-- сюда подгружаются города -->
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    const openBtn = document.getElementById('open-city-modal');
    const closeBtn = document.getElementById('close-city-modal');
    const modal = document.getElementById('city-modal');
    const list = document.getElementById('city-list');
    const search = document.getElementById('city-search');
    const currentName = document.getElementById('current-city-name');

    openBtn.addEventListener('click', ()=> modal.style.display = 'block');
    closeBtn.addEventListener('click', ()=> modal.style.display = 'none');
    modal.addEventListener('click', (e) => { if (e.target===modal) modal.style.display='none' });

    let lastQ = '';
    async function loadCities(q='') {
        if (q === lastQ) return;
        lastQ = q;
        list.innerHTML = '<div style="grid-column:1/-1;padding:1rem;text-align:center;">Загрузка…</div>';
        try {
            const url = "{{ route('cities.index') }}" + (q ? ('?q=' + encodeURIComponent(q)) : '');
            const res = await fetch(url, { headers: {'Accept':'application/json'}});
            const cities = await res.json();
            if (!cities.length) {
                list.innerHTML = '<div style="grid-column:1/-1;padding:1rem;text-align:center;">Ничего не найдено</div>';
                return;
            }
            list.innerHTML = cities.map(c => `
                <button class="city-item" data-id="${c.id}" style="text-align:left;padding:.6rem;border:1px solid #eee;border-radius:.4rem;background:#fafafa;">
                    ${c.name}
                </button>
            `).join('');
            document.querySelectorAll('.city-item').forEach(btn=>{
                btn.addEventListener('click', async () => {
                    const cityId = btn.dataset.id;
                    await setCity(cityId);
                });
            });
        } catch (err) {
            list.innerHTML = '<div style="grid-column:1/-1;padding:1rem;text-align:center;color:red;">Ошибка загрузки</div>';
            console.error(err);
        }
    }

    async function setCity(cityId) {
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch("{{ route('cities.set') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || ''
                },
                body: JSON.stringify({ city_id: cityId })
            });
            if (!res.ok) throw new Error('network');
            const json = await res.json();
            currentName.textContent = json.city.name;
            modal.style.display = 'none';
            // You may want to reload page or update other parts:
            // location.reload();
        } catch (err) {
            alert('Не удалось установить город. Попробуйте ещё раз.');
            console.error(err);
        }
    }

    // загрузка при открытии
    openBtn.addEventListener('click', ()=> loadCities());

    // поиск с задержкой
    let t;
    search.addEventListener('input', ()=> {
        clearTimeout(t);
        t = setTimeout(()=> loadCities(search.value.trim()), 300);
    });

})();
</script>
