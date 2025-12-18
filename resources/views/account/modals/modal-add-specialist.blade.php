




<div class="modal fade" id="addDoctorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

<form id="addDoctorForm"
      method="POST"
      action="/add-doctor"
      enctype="multipart/form-data">
    @csrf


                <div class="modal-header">
                    <h5 class="modal-title">Добавление специалиста</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="doctorErrors" class="alert alert-danger d-none"></div>
                    <div class="row g-3">

                        <div class="col-md-6">
    <label>Сфера деятельности</label>
    <select name="field_of_activity_id" id="fieldOfActivitySelect" class="form-select">
        <option value="">Загрузка...</option>
    </select>
</div>


                        <div class="col-12">
                            <label>Имя врача</label>
                            <input type="text" name="name" class="form-control">
                        </div>


<div class="col-md-6">
    <label>Дата рождения</label>
    <input 
        type="date"
        id="date_of_birth"
        name="date_of_birth"
        class="form-control"
        max="{{ \Carbon\Carbon::now()->subYears(18)->format('Y-m-d') }}"
    >
</div>
<div class="col-md-6">
    <label>Стаж (лет)</label>
    <input 
        type="number" 
        id="experience"
        name="experience" 
        class="form-control"
        min="0"
    >
</div>



                                            <div class="col-12">
    <label class="form-check-label">
        <input type="checkbox" name="its_me" class="form-check-input">
    <strong>
        Добавляю себя
    </strong> 
    <label for="its_me" class="label_its_me">Мы попросим вас подтвердить что именно вы явлетесь этим специалистом, для этого могут потребоваться фотографии дипломов и документов</label>
    </label>
</div>


                        <div class="col-md-6">
                            <label>Город</label>
                            <select name="city_id" id="citySelect" class="form-select">
                                <option value="">Выберите город</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Клиника</label>
                            <select name="clinic" id="clinicSelect" class="form-select">
    <option value="">Сначала выберите город</option>
</select>

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
                        <div class="col-6">
                            <label>Почта</label>
                            <input type="text" name="mail" class="form-control">
                        </div>    


                        <div class="col-md-6">
                            <label>Экзотические животные</label>
                            <select name="exotic_animals" class="form-control">
                                <option value="Нет">Нет</option>
                                <option value="Да">Да</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Выезд на дом</label>
                            <select name="On_site_assistance" class="form-control">
                                <option value="Нет">Нет</option>
                                <option value="Да">Да</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label>Фото</label>

                            <!-- Квадрат для выбора -->
                            <div id="photoPicker">+</div>

                            <!-- Скрытый input -->
                            <input type="file" id="doctorPhotoInput" name="photo" accept="image/*">

                            <!-- Превью -->
                            <img id="doctorPhotoPreview" title="Предпросмотр" class="mt-2">
                        </div>

                        <div class="col-12">
                            <label>Расскажите о специалисте</label>
                            <textarea name="description" rows="4" class="form-control" placeholder="Опишите род деятельности, направления"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Сохранить</button>

                </div>

            </form>

        </div>
    </div>
</div>
