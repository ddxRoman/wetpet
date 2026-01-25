import $ from 'jquery';
import select2 from 'select2';

// КРИТИЧЕСКИ ВАЖНО для Vite: привязываем select2 к нашему jQuery
if (typeof $.fn.select2 === 'undefined') {
    select2(window, $);
}

$(document).ready(function () {
    // Список всех селекторов, которые должны стать Select2
    const regionSelectors = '#regionSelect, #regionSelect_specialist';
    const citySelectors = '#citySelect, #citySelect_specialist';

    // Инициализация
    $(regionSelectors).select2({ width: '100%' });
    $(citySelectors).select2({ width: '100%' });

    console.log('🚀 ОТЛАДКА: Select2 проинициализирован для всех полей');

    // Слушаем изменение любого из регионов
    $(document).on('change', regionSelectors, function () {
        const region = $(this).val();
        const currentId = $(this).attr('id');
        
        // Определяем, какой именно селект города обновлять
        let $currentCitySelect;
        if (currentId === 'regionSelect_specialist') {
            $currentCitySelect = $('#citySelect_specialist');
        } else {
            $currentCitySelect = $('#citySelect');
        }

        console.log('📡 СОБЫТИЕ:', currentId, 'выбрал', region);

        if (!region) {
            $currentCitySelect.html('<option value="">Сначала выберите регион</option>').trigger('change');
            return;
        }

        $currentCitySelect.prop('disabled', true);
        $currentCitySelect.html('<option value="">Загрузка городов...</option>').trigger('change');

        // Запрос к API
        fetch(`/api/cities/by-region/${encodeURIComponent(region)}`)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">Выберите город</option>';
                data.forEach(city => {
                    options += `<option value="${city.id}">${city.name}</option>`;
                });

                $currentCitySelect.html(options).prop('disabled', false);
                
                // Обновляем визуальную часть Select2
                $currentCitySelect.trigger('change.select2'); 
                console.log('✨ Список обновлен для:', $currentCitySelect.attr('id'));
            })
            .catch(err => {
                console.error('❌ Ошибка загрузки городов:', err);
                $currentCitySelect.prop('disabled', false);
            });
    });
});