@vite(['resources/js/pages/edit_doctor.js'])

<form id="editDoctorForm"
      method="POST"
      action="{{ route('doctor.update', $doctor) }}"
      enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- Верхняя панель --}}
    <div class="d-flex btn-eye justify-content-between align-items-center mb-3 px-3">
        <h4 class="modal-title mb-0">Редактирование врача</h4>
        <div class="d-flex gap-2">
            @if($doctor->slug)
                <a href="{{ route('doctors.show', $doctor->slug) }}" 
                   target="_blank" 
                   class="btn-view-profile d-flex align-items-center justify-content-center shadow-sm"
                   title="Перейти к карточке врача">
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
            {{-- ФИО --}}
            <div class="col-md-8">
                <label class="form-label">ФИО врача</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $doctor->name ?? '') }}">
            </div>

            {{-- Специализация (сгруппированная) --}}
            <div class="col-md-4">
                <label class="form-label">Специализация</label>
                <select name="specialization" id="fieldOfActivitySelect" class="form-select" required>
                    <option value="">Выберите специализацию</option>
                    @isset($doctorFields)
                        @foreach($doctorFields as $category => $fields)
                            <optgroup label="{{ $category }}">
                                @foreach($fields as $field)
                                    <option value="{{ $field->name }}" 
                                        {{ (old('specialization', $doctor->specialization ?? '') == $field->name) ? 'selected' : '' }}>
                                        &#10148; {{ $field->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    @endisset
                </select>
            </div>

            {{-- Дата рождения --}}
            <div class="col-md-6">
                <label class="form-label">Дата рождения</label>
                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" 
                       value="{{ old('date_of_birth', isset($doctor->date_of_birth) ? \Carbon\Carbon::parse($doctor->date_of_birth)->format('Y-m-d') : '') }}">
            </div>

            @php
                $yearsOld = isset($doctor->date_of_birth) ? \Carbon\Carbon::parse($doctor->date_of_birth)->age : 0;
                $maxExperience = max(0, $yearsOld - 18);
            @endphp

            {{-- Стаж --}}
            <div class="col-md-6">
                <label class="form-label">Стаж (лет)</label>
                <input type="number" 
                       name="experience" 
                       id="experienceInput"
                       class="form-control" 
                       min="0"
                       max="{{ $maxExperience }}" 
                       value="{{ old('experience', $doctor->experience ?? '0') }}">
                <small class="text-muted">Максимум для данного возраста: {{ $maxExperience }} лет</small>
            </div>

            {{-- Экзотические животные --}}
<div class="col-md-3 d-flex align-items-end pb-2">
    <div class="form-check form-switch">
        {{-- Скрытое поле: если свитч выключен, отправится "Нет" --}}
        <input type="hidden" name="exotic_animals" value="Нет">
        <input class="form-check-input" 
               type="checkbox" 
               role="switch" 
               id="exoticAnimalsSwitch" 
               name="exotic_animals" 
               value="Да"
               {{ (old('exotic_animals', $doctor->exotic_animals ?? '') == 'Да') ? 'checked' : '' }}>
        <label class="form-check-label ms-2" for="exoticAnimalsSwitch">Экзотические животные</label>
    </div>
</div>

{{-- Выезд на дом --}}
<div class="col-md-3 d-flex align-items-end pb-2">
    <div class="form-check form-switch">
        {{-- Скрытое поле: если свитч выключен, отправится "Нет" --}}
        <input type="hidden" name="On_site_assistance" value="Нет">
        <input class="form-check-input" 
               type="checkbox" 
               role="switch" 
               id="onSiteAssistanceSwitch" 
               name="On_site_assistance" 
               value="Да"
               {{ (old('On_site_assistance', $doctor->On_site_assistance ?? '') == 'Да') ? 'checked' : '' }}>
        <label class="form-check-label ms-2" for="onSiteAssistanceSwitch">Выезд на дом</label>
    </div>
</div>


{{-- Поле Город --}}
<div class="col-md-6">
    <label class="form-label">Город</label>
    {{-- Добавили onchange для принудительного вызова функции, если основной JS не подцепился --}}
    <select name="city_id" 
            id="citySelect_specialist" 
            class="form-select" 
            onchange="if(typeof loadClinicsByCity === 'function') { loadClinicsByCity(this.value) }">
        <option value="">Выберите город</option>
        @isset($allCities)
            @foreach($allCities as $city)
                <option value="{{ $city->id }}" 
                    {{ (old('city_id', $doctor->city_id ?? '') == $city->id) ? 'selected' : '' }}>
                    {{ $city->name }} ({{ $city->region }})
                </option>
            @endforeach
        @endisset
    </select>
</div>

{{-- Поле Организация --}}
<div class="col-md-6">
    <label class="form-label">Организация (клиника)</label>
    <select name="organization_id" id="clinicSelect" class="form-select">
        <option value="">Выберите организацию</option>
        @isset($organizations)
            @foreach($organizations as $org)
                <option value="{{ $org->id }}" 
                    {{ (old('organization_id', $doctor->organization_id ?? '') == $org->id) ? 'selected' : '' }}>
                    {{ $org->name }}
                </option>
            @endforeach
        @endisset
    </select>
</div>

{{-- Маленький скрипт-подстраховка специально для этого блейда --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const citySelect = document.getElementById('citySelect_specialist');
        if (citySelect) {
            citySelect.addEventListener('change', function() {
                console.log('Город изменен на: ' + this.value); // Проверь это в консоли F12
            });
        }
    });
</script>

            {{-- Телефон --}}
            <div class="col-6">
                <label class="form-label">Телефон</label>
                <input type="phone" name="phone" class="form-control" 
                       value="{{ old('phone', $doctor->contacts->phone ?? '') }}">
            </div>

            {{-- Мессенджеры --}}
<div class="col-12 mt-2">
    <div class="accordion accordion-flush border-bottom" id="messengerAccordion">
        <div class="accordion-item" style="border: none;">
            <h2 class="accordion-header" id="flush-headingOne">
                <button class="accordion-button collapsed text-primary fw-bold ps-0" 
                        type="button" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#collapseMessengers" 
                        aria-expanded="false" 
                        aria-controls="collapseMessengers"
                        style="background: none; box-shadow: none; font-size: 0.9rem;">
                    + Добавить мессенджеры
                </button>
            </h2>
            {{-- Убрали класс show, теперь он всегда скрыт при загрузке --}}
            <div id="collapseMessengers" 
                 class="accordion-collapse collapse" 
                 aria-labelledby="flush-headingOne" 
                 data-bs-parent="#messengerAccordion">
                <div class="accordion-body px-0 py-3">
                    <div class="d-flex flex-column gap-3">
                        
                        {{-- Telegram --}}
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <img src="{{ Storage::url('icon/contacts/telegram.svg') }}" width="22">
                            </span>
                            <input type="text" name="telegram" class="form-control border-start-0" 
                                   placeholder="Никнейм или телефон Telegram"
                                   value="{{ old('telegram', $doctor->contacts->telegram ?? '') }}">
                        </div>

                        {{-- WhatsApp --}}
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <img src="{{ Storage::url('icon/contacts/whatsapp.svg') }}" width="22">
                            </span>
                            <input type="text" name="whatsapp" class="form-control border-start-0" 
                                   placeholder="Номер телефона WhatsApp"
                                   value="{{ old('whatsapp', $doctor->contacts->whatsapp ?? '') }}">
                        </div>

                        {{-- Max Messenger --}}
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <img src="{{ Storage::url('icon/contacts/max_messendger.svg') }}" width="22">
                            </span>
                            <input type="text" name="max" class="form-control border-start-0" 
                                   placeholder="Данные Max Messenger"
                                   value="{{ old('max', $doctor->contacts->max ?? '') }}">
                        </div>

                    </div>
                    <div class="form-text mt-2" style="font-size: 0.8rem;">
                        Укажите логин или номер, привязанный к мессенджеру.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

            {{-- Почта --}}
            <div class="col-6">
                <label class="form-label">Почта</label>
                <input type="text" name="email" class="form-control" 
                       value="{{ old('email', $doctor->contacts->email ?? '') }}">
            </div>



            {{-- Фото (ID полностью идентичны JS) --}}
            <div class="col-12 mt-3 photo-section-container">
                <label class="fw-bold mb-2">Фото врача</label>
                <div class="specialist-photo-container position-relative" style="width: 120px; height: 120px;">
                    <div id="photoPreviewWrapper" class="{{ $doctor->photo ? '' : 'd-none' }} w-100 h-100">
                        <img id="doctorPhotoPreview" 
                             src="{{ $doctor->photo ? Storage::url($doctor->photo) : '#' }}" 
                             class="w-100 h-100 border rounded"
                             style="object-fit: cover; display: block;">
                        <button type="button" id="removePhotoBtn" 
                                class="btn btn-danger position-absolute shadow d-flex align-items-center justify-content-center" 
                                style="top: -10px; right: -10px; border-radius: 50%; width: 24px; height: 24px; padding: 0; z-index: 10; font-size: 18px;">
                            &times;
                        </button>
                    </div>

                    <div id="photoPicker" 
                         class="photo-picker-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center border rounded" 
                         style="cursor: pointer; z-index: 5; background: transparent;">
                        <div class="photo-picker-bg {{ $doctor->photo ? 'd-none' : '' }}" 
                             style="position: absolute; top:0; left:0; width:100%; height:100%; background: #f8f9fa; border: 2px dashed #ccc !important; border-radius: 8px; z-index: -1;">
                        </div>
                        <span class="fs-2 fw-light text-secondary {{ $doctor->photo ? 'd-none' : '' }}">+</span>
                        <small class="word_edit_photo">{{ $doctor->photo ? 'Сменить' : 'Добавить' }}</small>
                    </div>
                </div>
                <input type="file" id="doctorPhotoInput" name="photo" accept="image/*" class="d-none">
            </div>

            {{-- Описание --}}
            <div class="col-12">
                <label class="form-label">Расскажите о враче</label>
                <textarea name="description" rows="4" class="form-control">{{ old('description', $doctor->description ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="modal-footer d-flex justify-content-between">
        <button type="button" class="btn btn-outline-danger" onclick="deleteDoctor ({{ $doctor->id }}) ">
            Удалить врача
        </button>
        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
    </div>
</form>

{{-- Форма удаления --}}
<form id="delete-doctor-form-{{ $doctor->id }}" 
      action="{{ route('doctor.destroy', $doctor) }}" 
      method="POST" 
      style="display: none;">
    @csrf
    @method('DELETE')
</form>