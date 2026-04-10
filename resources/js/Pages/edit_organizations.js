import $ from 'jquery';
import select2 from 'select2';

if (typeof $.fn.select2 === 'undefined') {
    select2(window, $);
}

window.deleteOrganization = function(id) {
    if (confirm('Вы уверены, что хотите удалить эту организацию?')) {
        const form = document.getElementById('delete-organization-form-' + id);
        if (form) form.submit();
    }
};

$(document).ready(function () {
    // Инициализация Select2
    $('#citySelect_organization, #fieldOfActivitySelect').select2({ 
        width: '100%' 
    });

    // Обработка мессенджеров
    $(document).on('change', '.messenger-icon input', function() {
        $(this).closest('.messenger-icon').toggleClass('active', this.checked);
    });

    // --- ФОТО / ЛОГОТИП ---
    $(document).on('click', '#photoPicker', function(e) {
        e.preventDefault();
        $(this).closest('.photo-section-container').find('#doctorPhotoInput').click();
    });

    $(document).on('change', '#doctorPhotoInput', function(e) {
        const file = e.target.files[0];
        const $container = $(this).closest('.photo-section-container');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                $container.find('#doctorPhotoPreview').attr('src', event.target.result);
                $container.find('#photoPreviewWrapper').removeClass('d-none').show();
                $container.find('.photo-picker-bg, .fs-2').addClass('d-none');
                $container.find('.word_edit_photo').text('Сменить');
            };
            reader.readAsDataURL(file);
        }
    });

    $(document).on('click', '#removePhotoBtn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const $container = $(this).closest('.photo-section-container');
        
        $container.find('#doctorPhotoInput').val(''); 
        $container.find('#doctorPhotoPreview').attr('src', '#'); 
        $container.find('#photoPreviewWrapper').addClass('d-none').hide();
        $container.find('.photo-picker-bg, .fs-2').removeClass('d-none');
        $container.find('.word_edit_photo').text('Добавить');
    });
});