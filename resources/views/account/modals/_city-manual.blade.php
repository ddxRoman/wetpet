
<div class="col-12" data-city-manual
     data-suggest-url="{{ route('api.cities.suggest') }}"
     data-quick-add-url="{{ route('api.cities.quick-add') }}">
    <label class="form-label fw-semibold" style="font-size:13px;color:#374151;">Или введите название города вручную</label>
    <div class="position-relative">
        <input type="text" name="city_name" class="form-control wpm-input" autocomplete="off" maxlength="120"
               placeholder="укажите название населенного пункта">
        <div class="list-group position-absolute w-100 shadow d-none" data-role="suggest"
             style="z-index:1060;max-height:230px;overflow:auto;top:100%;"></div>
    </div>
    <div class="mt-1" style="font-size:13px;display:none;" data-role="status"></div>
    <div class="text-muted mt-1" style="font-size:12px;">
        Если вашего города нет в списке выше — введите название: если такой город уже есть в базе, он появится в подсказках.
        Если его нет, нажмите «Добавить этот город» — он сохранится в базе в выбранном регионе и будет указан в карточке.
        Название, введённое вручную, используется вместо выбранного в списке.
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
        var addUrl  = root.dataset.quickAddUrl;
        var status  = root.querySelector('[data-role="status"]');
        var timer = null, requestId = 0;

        function showStatus(text, ok) {
            status.textContent = text;
            status.style.color = ok ? '#198754' : '#dc3545';
            status.style.display = text ? 'block' : 'none';
        }

        function firstError(data) {
            if (!data) return '';
            if (data.message && !data.errors) return data.message;
            var errors = data.errors ? Object.values(data.errors) : [];
            return errors.length ? [].concat(errors[0])[0] : (data.message || '');
        }

        // Кнопка «Добавить этот город»: сохраняет город в базе, дальше он крепится к карточке при отправке формы
        function addCity(term, btn) {
            btn.disabled = true;
            btn.textContent = 'Добавляем…';

            var tokenEl = document.querySelector('meta[name="csrf-token"]');

            fetch(addUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': tokenEl ? tokenEl.content : ''
                },
                body: JSON.stringify({ name: term, region: region ? region.value : '' })
            })
                .then(function (r) { return r.json().catch(function () { return {}; }).then(function (d) { return { status: r.status, ok: r.ok, data: d }; }); })
                .then(function (res) {
                    close();
                    if (res.status === 401) {
                        showStatus('Чтобы добавить город, войдите в аккаунт.', false);
                        return;
                    }
                    if (!res.ok) {
                        showStatus(firstError(res.data) || 'Не удалось добавить город. Попробуйте ещё раз.', false);
                        return;
                    }
                    input.value = res.data.name;   // каноническое название — оно уйдёт в city_name
                    showStatus(res.data.created
                        ? '✓ Город «' + res.data.name + '» добавлен и будет указан в карточке.'
                        : '✓ Город «' + res.data.name + '» уже есть в базе — он будет указан в карточке.', true);
                })
                .catch(function () {
                    close();
                    showStatus('Не удалось добавить город. Проверьте соединение и попробуйте ещё раз.', false);
                });
        }

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
                var box = document.createElement('div');
                box.className = 'list-group-item';

                var text = document.createElement('div');
                text.className = 'text-muted small mb-2';
                text.textContent = (items.length ? 'Нет точного совпадения. ' : 'Такого города нет в базе. ') + '«' + term + '»';

                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm btn-primary';
                btn.textContent = 'Добавить этот город';
                btn.addEventListener('mousedown', function (e) { e.preventDefault(); }); // не терять фокус поля
                btn.addEventListener('click', function () { addCity(term, btn); });

                box.appendChild(text);
                box.appendChild(btn);
                list.appendChild(box);
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
            showStatus('', true);
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
            if (citySel.value) { input.value = ''; close(); showStatus('', true); }
        });
        // Сменили регион — введённое название относилось к другому региону
        if (region) region.addEventListener('change', function () { input.value = ''; close(); showStatus('', true); });
    }

    function initAll() {
        document.querySelectorAll('[data-city-manual]').forEach(initCityManual);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initAll);
    else initAll();
})();
</script>
