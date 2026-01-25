@vite(['resources/js/pages/edit_doctor.js'])
<form id="addDoctorForm"
      method="POST"
      action="{{ route('specialist.update', $specialist) }}"
      enctype="multipart/form-data">
    @csrf

@method('PUT')

                    <h4 class="modal-title">Редактирование специалиста</h4>
                    <div id="doctorErrors" class="alert alert-danger d-none"></div>
                    
                    <div class="modal-body">
                        <div class="row g-3">
                    <div class="col-md-8">
                        <label>Имя врача</label>
                        <input type="text" name="name" class="form-control">
                    </div>

                        
                        <div class="col-md-4">
<select name="field_of_activity_id" id="fieldOfActivitySelect" class="form-select">
    <option value="">Выберите сферу</option>
    
    @foreach($groupedFields as $groupName => $fields)
        <optgroup label="{{ $groupName }}">
            @foreach($fields as $field)
                <option value="{{ $field->id }}" 
                    {{ (old('field_of_activity_id', $specialist->field_of_activity_id ?? '') == $field->id) ? 'selected' : '' }}>
                    {{ $field->name }}
                </option>
            @endforeach
        </optgroup>
    @endforeach
</select>
</div>
<br>

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


<div class="col-md-4">
    <label>Регион</label>
    <select name="region" id="regionSelect_specialist" class="form-select">
        <option value="">Выберите регион</option>
        @foreach($cities->unique('region') as $city)
            <option value="{{ $city->region }}">{{ $city->region }}</option>
        @endforeach
    </select>
</div>

<div class="col-md-4">
    <label>Город</label>
    <select name="city_id" id="citySelect_specialist" class="form-select">
        <option value="">Сначала выберите регион</option>
    </select>
</div>

                        <div class="col-md-4">
                            <label>Оргнанизация </label>
                            <select name="clinic_id" id="clinicSelect" class="form-select">
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

    <div class="photo-wrapper">
        <!-- Квадрат для выбора -->
        <div id="photoPicker">+</div>

        <!-- Превью -->
        <div id="photoPreviewWrapper">
            <img id="doctorPhotoPreview"  title="Предпросмотр">
            <button type="button" id="removePhotoBtn">&times;</button>
        </div>
    </div>

    <!-- Скрытый input -->
    <input type="file" id="doctorPhotoInput" name="photo" accept="image/*">
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

