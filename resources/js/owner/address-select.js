import $ from 'jquery';
import 'select2';

// Зависимые выпадающие списки с поиском в личном кабинете владельца:
//  1) Организация/Клиника: поля "Регион" и "Город" (обычный текст).
//  2) Врач/Специалист: "Регион" (только фильтр, не отправляется формой),
//     "Город" (city_id) и "Клиника"/"Организация" — зависит от города.
document.addEventListener('DOMContentLoaded', () => {
    function initSelect2($el, placeholder) {
        $el.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder,
            allowClear: true,
            language: {
                noResults: () => 'Ничего не найдено',
                searching: () => 'Поиск…',
            },
        });
    }

    // values: массив строк (для текстовых полей) или массив {id, name} (для select по ID)
    function fillSelect($el, values, selected, isIdBased) {
        // Запоминаем подпись текущего выбранного option (если он есть в разметке
        // от сервера) — пригодится, если такого значения не окажется в новом списке.
        const fallbackLabel = $el.find('option:selected').first().text() || selected;

        $el.empty();
        $el.append(new Option('', '', false, false));

        let found = false;
        values.forEach((item) => {
            const value = isIdBased ? item.id : item;
            const label = isIdBased ? item.name : item;
            const isSelected = selected !== null && selected !== undefined && String(value) === String(selected);
            if (isSelected) found = true;
            $el.append(new Option(label, value, isSelected, isSelected));
        });

        // Текущее значение могло не попасть в справочник (старые/ручные данные) —
        // оставляем его в списке выбранным, чтобы не потерять при сохранении.
        if (selected !== null && selected !== undefined && selected !== '' && !found) {
            $el.append(new Option(fallbackLabel, selected, true, true));
        }
    }

    /* ─────────────────────────────────────────────────────────────
       1) Организация / Клиника — «Регион» и «Город» текстом
    ───────────────────────────────────────────────────────────── */
    (function initOrgClinicAddress() {
        const $regionSelect = $('#owner-region-select');
        const $citySelect = $('#owner-city-select');
        if (!$regionSelect.length || !$citySelect.length) return;

        const currentRegion = $regionSelect.data('current') || '';
        const currentCity = $citySelect.data('current') || '';

        function loadCities(region, selectedCity) {
            if (!region) {
                fillSelect($citySelect, [], selectedCity, false);
                $citySelect.trigger('change.select2');
                return;
            }
            fetch('/api/cities/by-region/' + encodeURIComponent(region))
                .then((res) => res.json())
                .then((cities) => {
                    const names = Array.isArray(cities) ? cities.map((c) => c.name) : [];
                    fillSelect($citySelect, names, selectedCity, false);
                    $citySelect.trigger('change.select2');
                })
                .catch((err) => console.error('Не удалось загрузить города региона:', err));
        }

        initSelect2($regionSelect, 'Начните вводить регион...');
        initSelect2($citySelect, 'Сначала выберите регион');

        fetch('/api/regions')
            .then((res) => res.json())
            .then((regions) => {
                fillSelect($regionSelect, Array.isArray(regions) ? regions : [], currentRegion, false);
                $regionSelect.trigger('change.select2');
                loadCities(currentRegion, currentCity);
            })
            .catch((err) => console.error('Не удалось загрузить список регионов:', err));

        $regionSelect.on('change', function () {
            loadCities($(this).val(), null);
        });
    })();

    /* ─────────────────────────────────────────────────────────────
       2) Врач / Специалист — «Регион» (фильтр) → «Город» (city_id)
          → «Клиника»/«Организация» (по городу)
    ───────────────────────────────────────────────────────────── */
    (function initDoctorSpecialistAddress() {
        const $regionSelect = $('#owner-specialist-region-select');
        const $citySelect = $('#owner-specialist-city-select');
        // Ровно один из двух точно будет на странице (врач или специалист)
        const $workplaceSelect = $('#owner-doctor-workplace-select, #owner-specialist-workplace-select');
        if (!$regionSelect.length || !$citySelect.length) return;

        const currentRegion = $regionSelect.data('current') || '';
        const currentCityId = $citySelect.data('current') || '';
        const currentWorkplaceId = $workplaceSelect.data('current') || '';
        const isDoctor = $workplaceSelect.attr('id') === 'owner-doctor-workplace-select';

        initSelect2($regionSelect, 'Начните вводить регион...');
        initSelect2($citySelect, 'Начните вводить город...');
        if ($workplaceSelect.length) {
            initSelect2($workplaceSelect, isDoctor ? 'Начните вводить клинику...' : 'Начните вводить организацию...');
        }

        function loadWorkplaces(cityId, selectedId) {
            if (!$workplaceSelect.length) return;
            if (!cityId) {
                fillSelect($workplaceSelect, [], selectedId, true);
                $workplaceSelect.trigger('change.select2');
                return;
            }
            const url = isDoctor
                ? '/api/clinics/by-city/' + encodeURIComponent(cityId)
                : '/get-organizations-by-city-id/' + encodeURIComponent(cityId);

            fetch(url)
                .then((res) => res.json())
                .then((list) => {
                    fillSelect($workplaceSelect, Array.isArray(list) ? list : [], selectedId, true);
                    $workplaceSelect.trigger('change.select2');
                })
                .catch((err) => console.error('Не удалось загрузить список по городу:', err));
        }

        function loadCities(region, selectedCityId) {
            if (!region) {
                fillSelect($citySelect, [], selectedCityId, true);
                $citySelect.trigger('change.select2');
                loadWorkplaces(null, null);
                return;
            }
            fetch('/api/cities/by-region/' + encodeURIComponent(region))
                .then((res) => res.json())
                .then((cities) => {
                    fillSelect($citySelect, Array.isArray(cities) ? cities : [], selectedCityId, true);
                    $citySelect.trigger('change.select2');
                    // При первичной загрузке подтягиваем места работы под уже выбранный город
                    loadWorkplaces(selectedCityId || $citySelect.val(), currentWorkplaceId);
                })
                .catch((err) => console.error('Не удалось загрузить города региона:', err));
        }

        fetch('/api/regions')
            .then((res) => res.json())
            .then((regions) => {
                fillSelect($regionSelect, Array.isArray(regions) ? regions : [], currentRegion, false);
                $regionSelect.trigger('change.select2');
                loadCities(currentRegion, currentCityId);
            })
            .catch((err) => console.error('Не удалось загрузить список регионов:', err));

        // Смена региона — только фильтр по городу, старый город сбрасываем
        $regionSelect.on('change', function () {
            loadCities($(this).val(), null);
        });

        // Смена города — перезагружаем клиники/организации, старый выбор сбрасываем
        $citySelect.on('change', function () {
            loadWorkplaces($(this).val(), null);
        });
    })();
});
