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
            <div class="col-12">
                <label class="form-label mt-2">Мессенджеры на этом номере:</label>
                <div id="messendger" class="d-flex gap-3 mt-1 messenger-icons">
                    <label class="messenger-icon {{ ($doctor->contacts->telegram ?? false) ? 'active' : '' }}">
                        <input type="checkbox" name="telegram" value="1" class="d-none" 
                               {{ ($doctor->contacts->telegram ?? false) ? 'checked' : '' }}>
                        <img src="{{ Storage::url('icon/contacts/telegram.svg') }}" width="30">
                    </label>

                    <label class="messenger-icon {{ ($doctor->contacts->whatsapp ?? false) ? 'active' : '' }}">
                        <input type="checkbox" name="whatsapp" value="1" class="d-none" 
                               {{ ($doctor->contacts->whatsapp ?? false) ? 'checked' : '' }}>
                        <img src="{{ Storage::url('icon/contacts/whatsapp.svg') }}" width="30">
                    </label>

                    <label class="messenger-icon {{ ($doctor->contacts->max ?? false) ? 'active' : '' }}">
                        <input type="checkbox" name="max" value="1" class="d-none" 
                               {{ ($doctor->contacts->max ?? false) ? 'checked' : '' }}>
                        <img src="{{ Storage::url('icon/contacts/max_messendger.svg') }}" width="30">
                    </label>
                </div>
            </div>

            {{-- Почта --}}
            <div class="col-6">
                <label class="form-label">Почта</label>
                <input type="text" name="email" class="form-control" 
                       value="{{ old('email', $doctor->contacts->email ?? '') }}">
            </div>

            {{-- Селекты Да/Нет --}}
            <div class="col-md-3">
                <label class="form-label">Экзотические животные</label>
                <select name="exotic_animals" class="form-select">
                    <option value="Нет" {{ (old('exotic_animals', $doctor->exotic_animals ?? '') == 'Нет') ? 'selected' : '' }}>Нет</option>
                    <option value="Да" {{ (old('exotic_animals', $doctor->exotic_animals ?? '') == 'Да') ? 'selected' : '' }}>Да</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Выезд на дом</label>
                <select name="On_site_assistance" class="form-select">
                    <option value="Нет" {{ (old('On_site_assistance', $doctor->On_site_assistance ?? '') == 'Нет') ? 'selected' : '' }}>Нет</option>
                    <option value="Да" {{ (old('On_site_assistance', $doctor->On_site_assistance ?? '') == 'Да') ? 'selected' : '' }}>Да</option>
                </select>
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
        <button type="button" class="btn btn-outline-danger" onclick="deleteDoctor({{ $doctor->id }})">
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