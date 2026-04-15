@vite(['resources/js/pages/edit_doctor.js'])

<form id="addDoctorForm"
      method="POST"
      action="{{ route('specialist.update', $specialist) }}"
      enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- Верхняя панель с заголовком и кнопкой перехода --}}
<div class="d-flex btn-eye justify-content-between align-items-center mb-3 px-3">
    <h4 class="modal-title mb-0">Редактирование специалиста</h4>
    
    <div class="d-flex gap-2">
        @if($specialist->slug)
<a href="{{ route('doctors.show', $specialist->slug) }}" 
   target="_blank" 
   class="btn-view-profile d-flex align-items-center justify-content-center shadow-sm"
   title="Перейти к карточке специалиста">
    <img class="btn-eye-icon" src="{{ Storage::url('icon/button/eye.svg') }}" alt="Иконка глаз">
    <span class="btn-text">Просмотр анкеты</span>
</a>
        @else
            <span class="badge bg-warning text-dark d-flex align-items-center">
                Slug не сгенерирован
            </span>
        @endif
    </div>
</div>

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
                <select name="specialization" id="fieldOfActivitySelect" class="form-select">
                    <option value="">Выберите сферу</option>
                    @foreach($groupedFields as $groupName => $fields)
                            @foreach($fields as $field)
                                <option value="{{ $field->name }}" 
                                    {{ (old('specialization', $specialist->specialization ?? '') == $field->name) ? 'selected' : '' }}>
                                    &#10148;{{ $field->name }}
                                </option>
                            @endforeach
                    @endforeach
                </select>
            </div>

            {{-- Дата рождения --}}
            <div class="col-md-6">
                <label>Дата рождения</label>
                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" 
                       value="{{ old('date_of_birth', isset($specialist->date_of_birth) ? \Carbon\Carbon::parse($specialist->date_of_birth)->format('Y-m-d') : '') }}">
            </div>

{{-- Сначала рассчитаем лимит в начале файла --}}
@php
    $yearsOld = isset($specialist->date_of_birth) ? \Carbon\Carbon::parse($specialist->date_of_birth)->age : 0;
    $maxExperience = max(0, $yearsOld - 18);
@endphp

{{-- Поле Стаж --}}
<div class="col-md-6">
    <label>Стаж (лет)</label>
    <input type="number" 
           name="experience" 
           id="experienceInput"
           class="form-control" 
           min="0"
           max="{{ $maxExperience }}" 
           value="{{ old('experience', $specialist->experience ?? '0') }}">
    <small class="text-muted">Максимум для данного возраста: {{ $maxExperience }} лет</small>
</div>

{{-- Переключатели (Свитчи) --}}
<div class="col-md-6 d-flex align-items-center mt-3">
    <div class="form-check form-switch">
        <input type="hidden" name="exotic_animals" value="Нет">
        <input class="form-check-input" type="checkbox" role="switch" id="exoticAnimalsSwitch" name="exotic_animals" value="Да"
               {{ (old('exotic_animals', $specialist->exotic_animals ?? '') == 'Да') ? 'checked' : '' }}>
        <label class="form-check-label ms-2" for="exoticAnimalsSwitch">Экзотические животные</label>
    </div>
</div>

<div class="col-md-6 d-flex align-items-center mt-3">
    <div class="form-check form-switch">
        <input type="hidden" name="On_site_assistance" value="Нет">
        <input class="form-check-input" type="checkbox" role="switch" id="onSiteAssistanceSwitch" name="On_site_assistance" value="Да"
               {{ (old('On_site_assistance', $specialist->On_site_assistance ?? '') == 'Да') ? 'checked' : '' }}>
        <label class="form-check-label ms-2" for="onSiteAssistanceSwitch">Выезд на дом</label>
    </div>
</div>

            {{-- Поле Город --}}
            <div class="col-md-6">
                <label>Город</label>
                <select name="city_id" id="citySelect_specialist" class="form-select">
                    <option value="">Выберите город</option>
                    @foreach($allCities as $city)
                        <option value="{{ $city->id }}" 
                            {{ (old('city_id', $specialist->city_id ?? '') == $city->id) ? 'selected' : '' }}>
                            {{ $city->name }} ({{ $city->region }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Поле Организация --}}
            <div class="col-md-6">
                <label>Организация (клиника)</label>
                <select name="organization_id" id="clinicSelect" class="form-select">
                    <option value="">Выберите организацию</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}" 
                            {{ (old('organization_id', $specialist->organization_id ?? '') == $org->id) ? 'selected' : '' }}>
                            {{ $org->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Телефон --}}
            <div class="col-6">
                <label>Телефон</label>
                <input type="phone" name="phone" class="form-control" 
                       value="{{ old('phone', $specialist->contacts->phone ?? '') }}">
            </div>

{{-- Telegram --}}
<div class="input-group">
    <span class="input-group-text bg-light border-end-0">
        <img src="{{ Storage::url('icon/contacts/telegram.svg') }}" width="22">
    </span>
    <input type="text" name="telegram" class="form-control border-start-0" 
           placeholder="@username"
           value="{{ old('telegram', $specialist->contacts->telegram ?? '') }}">
</div>

{{-- WhatsApp --}}
<div class="input-group">
    <span class="input-group-text bg-light border-end-0">
        <img src="{{ Storage::url('icon/contacts/whatsapp.svg') }}" width="22">
    </span>
    {{-- Используем type="tel", чтобы на мобилках открывалась цифровая клавиатура --}}
    <input type="tel" name="whatsapp" class="form-control border-start-0" 
           placeholder="79991234567"
           value="{{ old('whatsapp', $specialist->contacts->whatsapp ?? '') }}">
</div>

{{-- Max Messenger --}}
<div class="input-group">
    <span class="input-group-text bg-light border-end-0">
        <img src="{{ Storage::url('icon/contacts/max_messendger.svg') }}" width="22">
    </span>
    <input type="text" name="max" class="form-control border-start-0" 
           placeholder="Введите текст"
           value="{{ old('max', $specialist->contacts->max ?? '') }}">
</div>

            {{-- Почта --}}
            <div class="col-6">
                <label>Почта</label>
                <input type="text" name="email" class="form-control" 
                       value="{{ old('email', $specialist->contacts->email ?? '') }}">
            </div> 

 {{-- Фото --}}
<div class="col-12 mt-3 photo-section-container">
    <label class="fw-bold mb-2">Фото специалиста</label>
    
    <div class="specialist-photo-container position-relative" style="width: 120px; height: 120px;">
        
        {{-- 1. Контейнер с превью --}}
        <div id="photoPreviewWrapper" class="{{ $specialist->photo ? '' : 'd-none' }} w-100 h-100">
            <img id="doctorPhotoPreview" 
                 src="{{ $specialist->photo ? Storage::url($specialist->photo) : '#' }}" 
                 class="w-100 h-100 border rounded"
                 style="object-fit: cover; display: block;">
            
            {{-- Кнопка удаления (Крестик) --}}
            <button type="button" id="removePhotoBtn" 
                    class="btn btn-danger position-absolute shadow d-flex align-items-center justify-content-center" 
                    style="top: -10px; right: -10px; border-radius: 50%; width: 24px; height: 24px; padding: 0; z-index: 10; font-size: 18px;">
                &times;
            </button>
        </div>

        {{-- 2. КНОПКА-НАЛОЖЕНИЕ --}}
        <div id="photoPicker" 
             class="photo-picker-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center border rounded" 
             style="cursor: pointer; z-index: 5; background: transparent;">
            
            <div class="photo-picker-bg {{ $specialist->photo ? 'd-none' : '' }}" 
                 style="position: absolute; top:0; left:0; width:100%; height:100%; background: #f8f9fa; border: 2px dashed #ccc !important; border-radius: 8px; z-index: -1;">
            </div>
            
            <span class="fs-2 fw-light text-secondary {{ $specialist->photo ? 'd-none' : '' }}">+</span>
            <small class="word_edit_photo">
                {{ $specialist->photo ? 'Сменить' : 'Добавить' }}
            </small>
        </div>
    </div>

    {{-- Скрытый инпут --}}
    <input type="file" id="doctorPhotoInput" name="photo" accept="image/*" class="d-none">
</div>

            {{-- Описание --}}
            <div class="col-12">
                <label>Расскажите о специалисте</label>
                <textarea name="description" rows="4" class="form-control">{{ old('description', $specialist->description ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="modal-footer d-flex justify-content-between">
        <button type="button" class="btn btn-outline-danger" onclick="deleteSpecialist({{ $specialist->id }})">
            Удалить специалиста
        </button>
        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
    </div>
</form>

{{-- Форма удаления СНАРУЖИ основной формы, но ВНУТРИ таба --}}
<form id="delete-specialist-form-{{ $specialist->id }}" 
      action="{{ route('specialist.destroy', $specialist) }}" 
      method="POST" 
      style="display: none;">
    @csrf
    @method('DELETE')
</form>