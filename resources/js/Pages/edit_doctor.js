import $ from 'jquery';
import select2 from 'select2';

if (typeof $.fn.select2 === 'undefined') {
    select2(window, $);
}

$(document).ready(function () {
    const regionSelectors = '#regionSelect, #regionSelect_specialist';
    const citySelectors = '#citySelect, #citySelect_specialist';

    $(regionSelectors).select2({ width: '100%' });
    $(citySelectors).select2({ width: '100%' });

    // 1. Логика Регион -> Город
    $(document).on('change', regionSelectors, function () {
        const region = $(this).val();
        const $currentCitySelect = ($(this).attr('id') === 'regionSelect_specialist') 
            ? $('#citySelect_specialist') 
            : $('#citySelect');

        if (!region) {
            $currentCitySelect.html('<option value="">Сначала выберите регион</option>').trigger('change');
            return;
        }

        $currentCitySelect.prop('disabled', true).html('<option>Загрузка...</option>').trigger('change');

        fetch(`/api/cities/by-region/${encodeURIComponent(region)}`)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">Выберите город</option>';
                data.forEach(city => {
                    options += `<option value="${city.id}">${city.name}</option>`;
                });
                $currentCitySelect.html(options).prop('disabled', false).trigger('change.select2');
            });
    });

    // 2. Логика Город -> Организация
    $(document).on('change', '#citySelect_specialist', function () {
        const cityId = $(this).val();
        const $clinicSelect = $('#clinicSelect');

        if (!cityId) {
            $clinicSelect.html('<option value="">Сначала выберите город</option>').trigger('change');
            return;
        }

        $clinicSelect.prop('disabled', true).html('<option>Загрузка...</option>').trigger('change');

        fetch(`/api/clinics/by-city/${cityId}`)
            .then(res => res.json())
            .then(data => {
                let options = '<option value="">Выберите организацию</option>';
                data.forEach(clinic => {
                    options += `<option value="${clinic.id}">${clinic.name}</option>`;
                });
                $clinicSelect.html(options).prop('disabled', false).trigger('change.select2');
            });
    });

    // 3. Логика мессенджеров (визуальный отклик)
    $(document).on('change', '.messenger-icon input', function() {
        $(this).closest('.messenger-icon').toggleClass('active', this.checked);
        // Можно добавить стили в CSS для .messenger-icon.active { opacity: 1; filter: grayscale(0); }
    });

    // 4. Логика Фото
    $('#photoPicker').on('click', function() {
        $('#doctorPhotoInput').click();
    });

    $('#doctorPhotoInput').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#doctorPhotoPreview').attr('src', e.target.result);
                $('#photoPreviewWrapper').removeClass('d-none');
            }
            reader.readAsDataURL(file);
        }
    });

    $('#removePhotoBtn').on('click', function() {
        $('#doctorPhotoInput').val('');
        $('#doctorPhotoPreview').attr('src', '#');
        $('#photoPreviewWrapper').addClass('d-none');
    });
});