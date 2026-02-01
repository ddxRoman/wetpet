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
            {{-- Имя --}}
            <div class="col-md-8">
                <label>Имя врача</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $specialist->name ?? '') }}">
            </div>

            {{-- Специализация --}}
            <div class="col-md-4">
                <label>Специализация</label>
{{-- Специализация --}}
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

            {{-- Дата рождения --}}
            <div class="col-md-6">
                <label>Дата рождения</label>
                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" 
                       value="{{ old('date_of_birth', isset($specialist->date_of_birth) ? \Carbon\Carbon::parse($specialist->date_of_birth)->format('Y-m-d') : '') }}">
            </div>

            {{-- Стаж --}}
            <div class="col-md-6">
                <label>Стаж (лет)</label>
                <input type="number" name="experience" class="form-control" value="{{ old('experience', $specialist->experience ?? '0') }}">
            </div>

            {{-- Регион --}}
            <div class="col-md-4">
                <label>Регион</label>
                <select name="region" id="regionSelect_specialist" class="form-select">
                    <option value="">Выберите регион</option>
                    @foreach($regions as $region)
                        <option value="{{ $region }}" {{ (optional($currentCity)->region == $region) ? 'selected' : '' }}>
                            {{ $region }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Город --}}
            <div class="col-md-4">
                <label>Город</label>
                <select name="city_id" id="citySelect_specialist" class="form-select">
                    <option value="">Сначала выберите регион</option>
                    @foreach($cities as $id => $cityName)
                        <option value="{{ $id }}" {{ (old('city_id', $specialist->city_id ?? '') == $id) ? 'selected' : '' }}>
                            {{ $cityName }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Организация --}}
            <div class="col-md-4">
                <label>Организация</label>
                <select name="organization_id" id="clinicSelect" class="form-select">
                    <option value="">Выберите организацию</option>
                    @foreach($organizations as $id => $orgName)
                        <option value="{{ $id }}" {{ (old('organization_id', $specialist->organization_id ?? '') == $id) ? 'selected' : '' }}>
                            {{ $orgName }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Контакты и Мессенджеры --}}
{{-- Телефон --}}
<div class="col-6">
    <label>Телефон</label>
    <input type="phone" name="phone" class="form-control" 
           value="{{ old('phone', $specialist->contacts->phone ?? '') }}">
</div>
                <label class="mt-2">Мессенджеры на этом номере:</label>
{{-- Мессенджеры --}}
<div id="messendger" class="d-flex gap-3 mt-1 messenger-icons">
    <label class="messenger-icon {{ ($specialist->contacts->telegram ?? false) ? 'active' : '' }}">
        <input type="checkbox" name="telegram" value="1" class="d-none" 
               {{ ($specialist->contacts->telegram ?? false) ? 'checked' : '' }}>
        <img src="{{ Storage::url('icon/contacts/telegram.svg') }}" width="30">
    </label>

    <label class="messenger-icon {{ ($specialist->contacts->whatsapp ?? false) ? 'active' : '' }}">
        <input type="checkbox" name="whatsapp" value="1" class="d-none" 
               {{ ($specialist->contacts->whatsapp ?? false) ? 'checked' : '' }}>
        <img src="{{ Storage::url('icon/contacts/whatsapp.svg') }}" width="30">
    </label>

    <label class="messenger-icon {{ ($specialist->contacts->max ?? false) ? 'active' : '' }}">
        <input type="checkbox" name="max" value="1" class="d-none" 
               {{ ($specialist->contacts->max ?? false) ? 'checked' : '' }}>
        <img src="{{ Storage::url('icon/contacts/max_messendger.svg') }}" width="30">
    </label>
</div>

{{-- Почта --}}
<div class="col-6">
    <label>Почта</label>
    <input type="text" name="email" class="form-control" 
           value="{{ old('email', $specialist->contacts->email ?? '') }}">
</div>

            {{-- Селекты Да/Нет --}}
            <div class="col-md-6">
                <label>Экзотические животные</label>
                <select name="exotic_animals" class="form-select">
                    <option value="Нет" {{ (old('exotic_animals', $specialist->exotic_animals ?? '') == 'Нет') ? 'selected' : '' }}>Нет</option>
                    <option value="Да" {{ (old('exotic_animals', $specialist->exotic_animals ?? '') == 'Да') ? 'selected' : '' }}>Да</option>
                </select>
            </div>

            <div class="col-md-6">
                <label>Выезд на дом</label>
                <select name="On_site_assistance" class="form-select">
                    <option value="Нет" {{ (old('On_site_assistance', $specialist->On_site_assistance ?? '') == 'Нет') ? 'selected' : '' }}>Нет</option>
                    <option value="Да" {{ (old('On_site_assistance', $specialist->On_site_assistance ?? '') == 'Да') ? 'selected' : '' }}>Да</option>
                </select>
            </div>

            {{-- Фото --}}
            <div class="col-12">
                <label>Фото специалиста</label>
                <div class="photo-wrapper d-flex align-items-center gap-3">
                    <div id="photoPicker" class="border d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; cursor: pointer; background: #f8f9fa;">
                        {{ $specialist->photo ? 'Сменить' : '+' }}
                    </div>

                    <div id="photoPreviewWrapper" class="{{ $specialist->photo ? '' : 'd-none' }}">
                        <img id="doctorPhotoPreview" 
                             src="{{ $specialist->photo ? Storage::url($specialist->photo) : '#' }}" 
                             style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                        <button type="button" id="removePhotoBtn" class="btn btn-sm btn-danger mt-1 d-block">Удалить</button>
                    </div>
                </div>
                <input type="file" id="doctorPhotoInput" name="photo" accept="image/*" class="d-none">
            </div>

            {{-- Описание --}}
            <div class="col-12">
                <label>Расскажите о специалисте</label>
                <textarea name="description" rows="4" class="form-control">{{ old('description', $specialist->description ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
    </div>
</form>