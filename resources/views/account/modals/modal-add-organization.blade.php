@vite(['resources/js/app.js'])

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

                        <div class="col-12">
                            <label class="form-check-label">
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
    <input type="phone" name="phone" class="form-control">
<label for="#messendger">Выберите соц сети к которым привязан этот контакт</label>
    <div id="messendger" class="d-flex gap-3 mt-2 messenger-icons">

        <!-- Telegram -->
        <label class="messenger-icon">
            <input type="checkbox" name="messengers[]" value="telegram" class="d-none">
            <img src="{{ Storage::url('icon/contacts/whatsapp.svg') }}" title="По этому номеру можно связатся в Телеграмм" alt="Telegram">
        </label>

        <!-- WhatsApp -->
        <label class="messenger-icon">
            <input type="checkbox" name="messengers[]" value="whatsapp" class="d-none">
            <img src="{{ Storage::url('icon/contacts/telegram.svg') }}" title="По этому номеру можно связатся в Вотсапп" alt="WhatsApp">
        </label>

        <!-- Messenger Max (VK Messenger) -->
        <label class="messenger-icon">
            <input type="checkbox" name="messengers[]" value="messenger" class="d-none">
            <img src="{{ Storage::url('icon/contacts/max_messendger.svg') }}" title="По этому номеру можно связатся в Max" alt="Messenger">
        </label>

    </div>
</div>

<style>
    .messenger-icons img {
        width: 36px;
        height: 36px;
        cursor: pointer;
        transition: 0.2s;
        opacity: 0.2;
    }
    .messenger-icons input:checked + img {
        opacity: 1;
        transform: scale(1.2);
    }
</style>

                            
                        <div class="col-6">
                            <label>Почта</label>
                            <input type="mail" name="mail" class="form-control">
                        </div>

                        <div class="col-12">
                            <label>Логотип</label>

                            <!-- Квадрат для выбора -->
                            <div id="photoPicker">+</div>

                            <!-- Скрытый input -->
                            <input type="file" id="doctorPhotoInput" name="logo" accept="image/*">

                            <!-- Превью -->
                            <img id="doctorPhotoPreview" title="Превью фото" class="mt-2">
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
