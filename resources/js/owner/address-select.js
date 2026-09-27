import $ from 'jquery';
import 'select2';

// Зависимые выпадающие списки «Регион» / «Город» с поиском
// в личном кабинете владельца (редактирование организации/клиники).
// Город показывает только населённые пункты выбранного региона.
document.addEventListener('DOMContentLoaded', () => {
    const $regionSelect = $('#owner-region-select');
    const $citySelect = $('#owner-city-select');

    // Защита: скрипт грузится глобально, но эти select'ы есть не на всех страницах
    if (!$regionSelect.length || !$citySelect.length) return;

    const currentRegion = $regionSelect.data('current') || '';
    const currentCity = $citySelect.data('current') || '';

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

    function fillSelect($el, values, selected) {
        $el.empty();
        $el.append(new Option('', '', false, false));

        let found = false;
        values.forEach((value) => {
            const isSelected = selected && value === selected;
            if (isSelected) found = true;
            $el.append(new Option(value, value, isSelected, isSelected));
        });

        // Текущее значение могло отсутствовать в справочнике (старые/ручные данные) —
        // всё равно показываем его выбранным, чтобы не потерять при сохранении.
        if (selected && !found) {
            $el.append(new Option(selected, selected, true, true));
        }
    }

    function loadCities(region, selectedCity) {
        if (!region) {
            fillSelect($citySelect, [], selectedCity);
            $citySelect.trigger('change.select2');
            return;
        }

        fetch('/api/cities/by-region/' + encodeURIComponent(region))
            .then((res) => res.json())
            .then((cities) => {
                const names = Array.isArray(cities) ? cities.map((c) => c.name) : [];
                fillSelect($citySelect, names, selectedCity);
                $citySelect.trigger('change.select2');
            })
            .catch((err) => console.error('Не удалось загрузить города региона:', err));
    }

    initSelect2($regionSelect, 'Начните вводить регион...');
    initSelect2($citySelect, 'Сначала выберите регион');

    // Первичная загрузка справочника регионов
    fetch('/api/regions')
        .then((res) => res.json())
        .then((regions) => {
            fillSelect($regionSelect, Array.isArray(regions) ? regions : [], currentRegion);
            $regionSelect.trigger('change.select2');
            // Города для уже выбранного (текущего) региона
            loadCities(currentRegion, currentCity);
        })
        .catch((err) => console.error('Не удалось загрузить список регионов:', err));

    // При смене региона — перезагружаем список городов.
    // Текущий город сбрасываем, т.к. он относился к старому региону.
    $regionSelect.on('change', function () {
        const region = $(this).val();
        loadCities(region, null);
    });
});
