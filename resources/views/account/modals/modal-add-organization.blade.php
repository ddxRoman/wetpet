@vite(['resources/js/app.js'])

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="modal fade" id="addOrganizationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form id="addOrganizationForm"
                method="POST"
                action="/add-organization"
                enctype="multipart/form-data">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Добавление Организации</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="doctorErrors" class="alert alert-danger d-none"></div>

                    <div class="row g-3">

                        <div class="col-12 form-check-label">
                            <label>
                                <input type="checkbox" name="its_me" class="form-check-input">
                                <strong>Это моя организация</strong>
                                <div class="label_its_me">
                                    Мы попросим вас подтвердить что вы владелец или доверенное лицо организации
                                </div>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label>Сфера деятельности</label>
                            <select name="field_of_activity_id" id="fieldOfActivitySelect" class="form-select">
                                <option value="">Загрузка...</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label>Название организации</label>
                            <input type="text" name="name" class="form-control">
                        </div>

                        {{-- region / city dependent selects --}}
                        <div class="col-md-6">
                            <label>Регион</label>
                            <select name="region" id="regionSelect" class="form-select">
                                <option value="">Выберите регион</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->region }}">{{ $city->region }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Город</label>
                            <select name="city_id" id="citySelect" class="form-select">
                                <option value="">Сначала выберите регион</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label>Улица</label>
                            <input type="text" name="street" class="form-control">
                        </div>
                        <div class="col-6">
                            <label>Дом</label>
                            <input type="text" name="house" class="form-control">
                        </div>                        
                        
<div class="col-6">
    <label>Телефон</label>
    <input type="phone" name="phone1" class="form-control">
<label for="#messendger">Выберите соц сети к которым привязан этот контакт</label>
    <div id="messendger" class="d-flex gap-3 mt-2 messenger-icons">

        <!-- Telegram -->
        <label class="messenger-icon">
            <input type="checkbox" name="messengers[]" value="telegram" class="d-none">
            <img src="{{ Storage::url('icon/contacts/telegram.svg') }}" title="По этому номеру можно связатся в Телеграмм" alt="Telegram">
        </label>

        <!-- VK -->
        <!-- <label class="messenger-icon">
            <input type="checkbox" name="messengers[]" value="VK" class="d-none">
            <img class="vk-logo-modal-img" src="{{ Storage::url('icon/contacts/vk-logo.svg') }}" title="Ссылка на страницу в ВК" alt="VK">
        </label> -->

        <!-- Messenger Max (VK Messenger) -->
        <label class="messenger-icon">
            <input type="checkbox" name="messengers[]" value="messenger" class="d-none">
            <img src="{{ Storage::url('icon/contacts/max_messendger.svg') }}" title="По этому номеру можно связатся в Max" alt="Messenger">
        </label>
    </div>
        <label>Телефон</label>
    <input type="phone" name="phone2" class="form-control">
</div>

{{-- Расписание  --}}
<div class="col-md-6">
    <label class="fw-bold">График работы (часы)</label>
    <input type="text" name="schedule" class="form-control" placeholder="09:00-20:00">
</div>
<div class="col-md-6">
    <label class="fw-bold">Рабочие дни</label>
    <input type="text" name="workdays" class="form-control" placeholder="Пн-Пт">
</div>

<style>
.photo-wrapper {
    position: relative;
    width: 150px;
    height: 150px;
}

#orgPhotoPicker {
    width: 150px;
    height: 150px;
    border: 2px dashed #ccc;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    cursor: pointer;
}

#orgPhotoPreviewWrapper {
    position: relative;
    width: 150px;
    height: 150px;
}

#orgPhotoPreview {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 4px;
}

#orgRemovePhotoBtn {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: none;
    background: #dc3545;
    color: #fff;
    font-size: 18px;
    cursor: pointer;
}

</style>

                            
                        <div class="col-6">
                            <label>Почта</label>
                            <input type="mail" name="email" class="form-control">
                        </div>

                        <div class="col-12">
                            <div class="photo-wrapper">
        <!-- Квадрат для выбора -->
       <div class="titile_add_photo" id="orgPhotoPicker">Логотип</div>

        <!-- Превью -->
    <div id="orgPhotoPreviewWrapper" style="display:none">
        <img id="orgPhotoPreview" title="Предпросмотр">
        <button type="button" id="orgRemovePhotoBtn">&times;</button>
    </div>
</div>

    <!-- Скрытый input -->
    <input type="file" id="orgPhotoInput" name="logo" accept="image/*">
</div>

                        <div class="col-12">
                            <label>Расскажите об организации</label>
                            <textarea name="description" rows="4" class="form-control" placeholder="Опишите род деятельности"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Сохранить</button>
                </div>

            </form>

        </div>
    </div>
</div>

<!-- Cropper modal (если используется) -->
<div id="cropper-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); justify-content:center; align-items:center; z-index:2000;">
    <div style="background:#fff; padding:20px; border-radius:10px; max-width:90%; max-height:90%;">
        <img id="cropper-image" title="Редактировать" style="max-width:100%; max-height:70vh;">
        <div class="mt-3 d-flex justify-content-between">
            <button class="btn btn-secondary" id="close-cropper">Отмена</button>
            <button class="btn btn-primary" id="save-cropped">Обрезать и сохранить</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('addOrganizationForm');
    
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // Останавливаем стандартную отправку

            const formData = new FormData(form);
            const isItsMeChecked = form.querySelector('input[name="its_me"]').checked;

            // Очищаем прошлые ошибки
            const errorBlock = document.getElementById('doctorErrors');
            if (errorBlock) {
                errorBlock.classList.add('d-none');
                errorBlock.innerHTML = '';
            }

            // Отправляем форму через AJAX (fetch)
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async response => {
                const data = await response.json();
                
                if (response.ok) {
                    // ЕСЛИ ВСЁ ПРОШЛО УСПЕШНО:
                    if (isItsMeChecked) {
                        // Если галочка активна — редиректим в кабинет владельца
                        window.location.href = "{{ route('owner.index') }}";
                    } else {
                        // Если галочка не активна — просто перезагружаем страницу или редиректим куда нужно
                        window.location.reload(); 
                    }
                } else {
                    // ЕСЛИ ЕСТЬ ОШИБКИ ВАЛИДАЦИИ:
                    if (errorBlock) {
                        errorBlock.classList.remove('d-none');
                        if (data.errors) {
                            let errorsHtml = '<ul>';
                            Object.values(data.errors).forEach(errArr => {
                                errArr.forEach(err => {
                                    errorsHtml += `<li>${err}</li>`;
                                });
                            });
                            errorsHtml += '</ul>';
                            errorBlock.innerHTML = errorsHtml;
                        } else {
                            errorBlock.innerText = data.message || 'Произошла ошибка при сохранении.';
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (errorBlock) {
                    errorBlock.classList.remove('d-none');
                    errorBlock.innerText = 'Системная ошибка при отправке формы.';
                }
            });
        });
    }
});
</script>