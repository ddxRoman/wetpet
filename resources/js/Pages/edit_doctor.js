import $ from 'jquery';
import select2 from 'select2';

if (typeof $.fn.select2 === 'undefined') {
    select2(window, $);
}

/**
 * Глобальная функция удаления специалиста
 */
window.deleteSpecialist = function(id) {
    if (confirm('Вы уверены, что хотите удалить этого специалиста? Это действие нельзя будет отменить.')) {
        const form = document.getElementById('delete-specialist-form-' + id);
        if (form) {
            form.submit();
        }
    }
};

$(document).ready(function () {
    const regionSelector = '#regionSelect_specialist';
    const citySelector = '#citySelect_specialist';
    const clinicSelector = '#clinicSelect';

    // Флаг "Первой загрузки". Пока он true, мы не делаем AJAX-запросы, 
    // чтобы не перезаписывать данные, которые PHP уже правильно вывел в Blade.
    let isInitialLoad = true;

    // Инициализируем Select2
    $(regionSelector + ', ' + citySelector + ', ' + clinicSelector).select2({ 
        width: '100%' 
    });

    // 1. Логика: РЕГИОН -> ГОРОД
    $(document).on('change', regionSelector, function () {
        const region = $(this).val();
        const $citySelect = $(citySelector);

        if (!region) {
            $citySelect.html('<option value="">Сначала выберите регион</option>').trigger('change');
            return;
        }

        // Если это первая загрузка и PHP уже прислал список городов для этого региона,
        // мы просто пропускаем fetch, чтобы не затирать выбранный город.
        if (isInitialLoad && $citySelect.children('option').length > 1) {
            console.log('JS: Города уже загружены через Blade, пропускаем fetch');
            return;
        }

        $citySelect.prop('disabled', true).html('<option>Загрузка городов...</option>').trigger('change');

        fetch(`/api/cities/by-region/${encodeURIComponent(region)}`)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">Выберите город</option>';
                data.forEach(city => {
                    options += `<option value="${city.id}">${city.name}</option>`;
                });
                // Сбрасываем флаг только после того, как пользователь реально изменил регион вручную
                isInitialLoad = false; 
                $citySelect.html(options).prop('disabled', false).trigger('change.select2');
            });
    });

    // 2. Логика: ГОРОД -> ОРГАНИЗАЦИЯ
    $(document).on('change', citySelector, function () {
        const cityId = $(this).val();
        const $clinicSelect = $(clinicSelector);

        // Если это загрузка страницы и клиники уже есть — ничего не трогаем
        if (isInitialLoad && $clinicSelect.children('option').length > 1) {
            isInitialLoad = false; // После проверки города и клиник при загрузке — выключаем флаг
            return;
        }
        
        isInitialLoad = false;

        if (!cityId) {
            $clinicSelect.html('<option value="">Сначала выберите город</option>').trigger('change');
            return;
        }

        $clinicSelect.prop('disabled', true).html('<option>Загрузка клиник...</option>').trigger('change');

        // fetch по маршруту, который ищет организации по ИМЕНИ города (через его ID)
        fetch(`/get-organizations-by-city-id/${cityId}`)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">Выберите организацию</option>';
                data.forEach(clinic => {
                    options += `<option value="${clinic.id}">${clinic.name}</option>`;
                });
                $clinicSelect.html(options).prop('disabled', false).trigger('change.select2');
            });
    });

    // Стаж работы расчитывается по формуле
    $('#date_of_birth').on('change', function() {
    const dob = new Date($(this).val());
    const today = new Date();
    
    if (isNaN(dob.getTime())) return;

    // Считаем возраст
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
        age--;
    }

    // Лимит стажа (Возраст - 18)
    const maxExp = Math.max(0, age - 18);
    
    const $expInput = $('#experienceInput');
    $expInput.attr('max', maxExp);
    
    // Если текущее значение стажа больше нового лимита — сбрасываем его
    if (parseInt($expInput.val()) > maxExp) {
        $expInput.val(maxExp);
    }
    
    // Обновляем текстовую подсказку (если она есть)
    $expInput.siblings('.text-muted').text(`Максимум для данного возраста: ${maxExp} лет`);
});

    // 3. Мессенджеры и Фото (оставляем без изменений)
    $(document).on('change', '.messenger-icon input', function() {
        $(this).closest('.messenger-icon').toggleClass('active', this.checked);
    });

    // Инициализация кроппера для фото врача
const fileInput = document.getElementById('doctorPhotoInput');
const previewImg = document.getElementById('doctorPhotoPreview');

if (fileInput && previewImg) {
    initCropper(fileInput, previewImg);
}

// Показ/скрытие обертки превью при изменении src (для кроппера)
const observer = new MutationObserver(() => {
    if (previewImg.src && previewImg.src !== window.location.href && previewImg.src.indexOf('#') === -1) {
        $('#photoPreviewWrapper').removeClass('d-none');
    }
});
observer.observe(previewImg, { attributes: true, attributeFilter: ['src'] });
});
$('#removePhotoBtn').on('click', function(e) {
    e.preventDefault();
    $('#doctorPhotoInput').val(''); // Очищаем инпут
    $('#doctorPhotoPreview').attr('src', '#'); // Сбрасываем картинку
    $('#photoPreviewWrapper').addClass('d-none'); // Скрываем обертку
    
    // Если хочешь реально удалять файл из БД при нажатии на крестик (без сохранения всей формы),
    // здесь нужно будет добавить AJAX-запрос. 
    // Но сейчас при нажатии "Сохранить изменения" с пустым инпутом старое фото останется.
});