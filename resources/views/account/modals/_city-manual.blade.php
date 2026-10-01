{{--
    Ручной ввод города (для модалок «Добавление специалиста» и «Добавление организации»).
    Список городов региона остаётся как был; здесь можно ввести название вручную:
      • при вводе идёт поиск по вхождению в названии (в пределах выбранного региона);
      • найденные города показываются подсказками — можно выбрать;
      • если города нет или он не выбран из подсказок — на сервере создаётся новый город
        (таблица cities, large_city = 0) в выбранном регионе.
    Поле уходит на сервер как city_name; если оно заполнено, оно главнее выбранного в списке.
    Скрипт не зависит от Vite-сборки и Choices: работает со значениями select[name=region] / select[name=city_id].
--}}
<div class="col-12" data-city-manual data-suggest-url="{{ route('api.cities.suggest') }}">
    <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Или введите название города вручную</label>
    <div class="position-relative">
        <input type="text" name="city_name" class="form-control wpm-input" autocomplete="off" maxlength="120"
               placeholder="Например: станица Динская">
        <div class="list-group position-absolute w-100 shadow d-none" data-role="suggest"
             style="z-index:1060;max-height:230px;overflow:auto;top:100%;"></div>
    </div>
    <div class="text-muted mt-1" style="font-size:12px;">
        Если вашего города нет в списке выше — введите название: если такой город уже есть в базе, он появится в подсказках,
        если нет — будет добавлен новый в выбранный регион. Название, введённое вручную, используется вместо выбранного в списке.
    </div>
</div>

<script>
(function () {
    function initCityManual(root) {
        if (root.dataset.ready) return;
        root.dataset.ready = '1';

        var scope   = root.closest('.modal') || document;
        var region  = scope.querySelector('select[name="region"]');
        var citySel = scope.querySelector('select[name="city_id"]');
        var input   = root.querySelector('input[name="city_name"]');
        var list    = root.querySelector('[data-role="suggest"]');
        var url     = root.dataset.suggestUrl;
        var timer = null, requestId = 0;

        function close() { list.classList.add('d-none'); list.innerHTML = ''; }

        function note(text) {
            var el = document.createElement('div');
            el.className = 'list-group-item text-muted small';
            el.textContent = text;
            return el;
        }

        function render(items, term) {
            list.innerHTML = '';
            var exact = false;

            items.forEach(function (c) {
                if (c.name.toLowerCase() === term.toLowerCase()) exact = true;
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action';
                btn.textContent = c.name;
                btn.addEventListener('mousedown', function (e) {
                    e.preventDefault();          // не терять фокус до подстановки
                    input.value = c.name;        // точное название: на сервере найдётся существующий город
                    close();
                });
                list.appendChild(btn);
            });

            if (!exact) {
                list.appendChild(note(items.length
                    ? 'Не нашли свой город? Оставьте введённое название — он будет добавлен.'
                    : 'Такого города нет в базе — при сохранении он будет добавлен в выбранный регион.'));
            }
            list.classList.remove('d-none');
        }

        function search() {
            var term = input.value.trim();
            if (term.length < 2) { close(); return; }

            list.innerHTML = '';
            if (!region || !region.value) {
                list.appendChild(note('Сначала выберите регион.'));
                list.classList.remove('d-none');
                return;
            }

            var current = ++requestId;
            var params = new URLSearchParams({ q: term, region: region.value });

            fetch(url + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (items) {
                    if (current !== requestId) return; // устаревший ответ
                    render(Array.isArray(items) ? items : [], term);
                })
                .catch(function () { close(); });
        }

        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(search, 250);
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter')  e.preventDefault();  // Enter не должен отправлять форму
            if (e.key === 'Escape') close();
        });
        input.addEventListener('blur', function () { setTimeout(close, 150); });

        // Выбрали город в списке — ручной ввод очищаем (одно из двух)
        if (citySel) citySel.addEventListener('change', function () {
            if (citySel.value) { input.value = ''; close(); }
        });
        // Сменили регион — введённое название относилось к другому региону
        if (region) region.addEventListener('change', function () { input.value = ''; close(); });
    }

    function initAll() {
        document.querySelectorAll('[data-city-manual]').forEach(initCityManual);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initAll);
    else initAll();
})();
</script>
