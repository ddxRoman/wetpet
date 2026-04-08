import $ from 'jquery';
import select2 from 'select2';

if (typeof $.fn.select2 === 'undefined') {
    select2(window, $);
}

window.deleteSpecialist = function(id) {
    if (confirm('Вы уверены, что хотите удалить этого специалиста? Это действие нельзя будет отменить.')) {
        const form = document.getElementById('delete-specialist-form-' + id);
        if (form) form.submit();
    }
};

$(document).ready(function () {
    const regionSelector = '#regionSelect_specialist';
    const citySelector = '#citySelect_specialist';
    const clinicSelector = '#clinicSelect';
    let isInitialLoad = true;

    // Инициализация Select2
    $(regionSelector + ', ' + citySelector + ', ' + clinicSelector).select2({ 
        width: '100%' 
    });

    // 1. РЕГИОН -> ГОРОД
    $(document).on('change', regionSelector, function () {
        const region = $(this).val();
        const $citySelect = $(citySelector);
        if (!region) return;
        if (isInitialLoad && $citySelect.children('option').length > 1) return;

        $citySelect.prop('disabled', true).trigger('change');
        fetch(`/api/cities/by-region/${encodeURIComponent(region)}`)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">Выберите город</option>';
                data.forEach(city => {
                    options += `<option value="${city.id}">${city.name}</option>`;
                });
                isInitialLoad = false; 
                $citySelect.html(options).prop('disabled', false).trigger('change.select2');
            });
    });

    // 2. ГОРОД -> ОРГАНИЗАЦИЯ
    $(document).on('change', citySelector, function () {
        const cityId = $(this).val();
        const $clinicSelect = $(clinicSelector);
        if (isInitialLoad && $clinicSelect.children('option').length > 1) {
            isInitialLoad = false;
            return;
        }
        isInitialLoad = false;
        if (!cityId) return;

        $clinicSelect.prop('disabled', true).trigger('change');
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

    // 3. СТАЖ
    $('#date_of_birth').on('change', function() {
        const dob = new Date($(this).val());
        const today = new Date();
        if (isNaN(dob.getTime())) return;
        let age = today.getFullYear() - dob.getFullYear();
        if (new Date(today.getFullYear(), today.getMonth(), today.getDate()) < new Date(today.getFullYear(), dob.getMonth(), dob.getDate())) age--;
        const maxExp = Math.max(0, age - 18);
        const $expInput = $('#experienceInput');
        $expInput.attr('max', maxExp);
        if (parseInt($expInput.val()) > maxExp) $expInput.val(maxExp);
        $expInput.siblings('.text-muted').text(`Максимум для данного возраста: ${maxExp} лет`);
    });

    // 4. МЕССЕНДЖЕРЫ
    $(document).on('change', '.messenger-icon input', function() {
        $(this).closest('.messenger-icon').toggleClass('active', this.checked);
    });

    // --- ФОТО (ФИНАЛЬНЫЙ ИСПРАВЛЕННЫЙ БЛОК) ---

    // Клик по зоне выбора (через контейнер для точности)
    $(document).on('click', '#photoPicker', function(e) {
        e.preventDefault();
        $(this).closest('.photo-section-container').find('#doctorPhotoInput').click();
    });

    // СОБЫТИЕ ВЫБОРА ФАЙЛА
    $(document).on('change', '#doctorPhotoInput', function(e) {
        const file = e.target.files[0];
        const $container = $(this).closest('.photo-section-container');
        const $preview = $container.find('#doctorPhotoPreview');
        const $wrapper = $container.find('#photoPreviewWrapper');
        const $bg = $container.find('.photo-picker-bg');
        const $plusSign = $container.find('.fs-2');
        const $smallText = $container.find('small');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                // Обновляем картинку
                $preview.attr('src', event.target.result);
                
                // Показываем превью и кнопку удаления
                $wrapper.removeClass('d-none').show();
                
                // Скрываем серый фон и плюс
                $bg.addClass('d-none');
                $plusSign.addClass('d-none');
                
                // Меняем текст на "Сменить"
                $smallText.text('Сменить');
                console.log('JS: Новое фото отрисовано');
            };
            reader.readAsDataURL(file);
        }
    });

    // Кнопка удаления
    $(document).on('click', '#removePhotoBtn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $container = $(this).closest('.photo-section-container');
        
        // Очищаем инпут и картинку
        $container.find('#doctorPhotoInput').val(''); 
        $container.find('#doctorPhotoPreview').attr('src', '#'); 
        
        // Скрываем обертку превью
        $container.find('#photoPreviewWrapper').addClass('d-none').hide();
        
        // Возвращаем серый фон и плюс
        $container.find('.photo-picker-bg').removeClass('d-none');
        $container.find('.fs-2').removeClass('d-none');
        $container.find('small').text('Добавить');
        
        console.log('JS: Фото полностью удалено');
    });

});