@vite(['resources/js/pages/edit_organizations.js'])


<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

@if(isset($clinic) && $clinic)
    <form id="editClinicForm"
          method="POST"
          action="{{ route('clinics.update', $clinic->id) }}"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        {{-- Верхняя панель --}}
        <div class="d-flex btn-eye justify-content-between align-items-center mb-4 px-3">
            <h4 class="modal-title mb-0">Редактирование клиники</h4>
            
            <div class="d-flex gap-2">
                @if($clinic->slug)
                    <a href="{{ route('clinics.show', $clinic->slug) }}" 
                       target="_blank" 
                       class="btn-view-profile d-flex align-items-center justify-content-center shadow-sm"
                       title="Перейти к карточке клиники">
                        <img class="btn-eye-icon" src="{{ Storage::url('icon/button/eye.svg') }}" alt="Иконка глаз">
                        <span class="btn-text">Просмотр профиля</span>
                    </a>
                @else
                    <span class="badge bg-warning text-dark d-flex align-items-center">
                        Ссылка (slug) не создана
                    </span>
                @endif
            </div>
        </div>

        <div id="clinicErrors" class="alert alert-danger d-none"></div>

        <div class="modal-body">
            <div class="row g-3">
                {{-- Название --}}
                <div class="col-md-8">
                    <label class="fw-bold">Название клиники</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $clinic->name) }}" required>
                </div>

                {{-- Сфера (для клиник она фиксированная) --}}
                <div class="col-md-4">
                    <label class="fw-bold">Сфера деятельности</label>
                    <input type="text" class="form-control" value="Ветеринарная клиника" readonly disabled>
                </div>

                {{-- Город --}}
<div class="col-md-6">
    <label class="fw-bold">Город</label>
    <select name="city_id" id="citySelect_clinic" class="form-select select2-init" required>
        <option value="">Введите название города...</option>
        @foreach($allCities as $city)
            <option value="{{ $city->id }}" 
                {{ (old('city_id') == $city->id || (isset($clinic) && $clinic->city == $city->name && $clinic->region == $city->region)) ? 'selected' : '' }}>
                {{ $city->name }} ({{ $city->region }})
            </option>
        @endforeach
    </select>
</div>

                {{-- Адрес --}}
                <div class="col-md-4">
                    <label class="fw-bold">Улица</label>
                    <input type="text" name="street" class="form-control" value="{{ old('street', $clinic->street) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="fw-bold">Дом</label>
                    <input type="text" name="house" class="form-control" value="{{ old('house', $clinic->house) }}" required>
                </div>

                {{-- Контакты --}}

<div class="col-md-6">
    <label class="fw-bold">Основной телефон</label>
    <input type="text" name="phone1" class="form-control" value="{{ old('phone1', $clinic->phone1) }}">
</div>
<div class="col-md-6">
    <label class="fw-bold">Дополнительный телефон</label>
    <input type="text" name="phone2" class="form-control" value="{{ old('phone2', $clinic->phone2) }}">
</div>
                <div class="col-md-6">
                    <label class="fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $clinic->email) }}">
                </div>

                {{-- Расписание --}}
                <div class="col-md-6">
                    <label class="fw-bold">График работы (время)</label>
                    <input type="text" name="schedule" class="form-control" value="{{ old('schedule', $clinic->schedule) }}" placeholder="Например: с 8:00 до 22:00">
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Рабочие дни</label>
                    <input type="text" name="workdays" class="form-control" value="{{ old('workdays', $clinic->workdays) }}" placeholder="Например: Пн-Вс">
                </div>

                {{-- Логотип --}}
                <div class="col-12 mt-3">
                    <label class="fw-bold mb-2">Логотип</label>
                    <div class="d-flex align-items-center gap-3">
                        @if($clinic->logo)
                            <img src="{{ Storage::url($clinic->logo) }}" alt="Logo" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                        @endif
                        <input type="file" name="logo" class="form-control" accept="image/*">
                    </div>
                </div>

                {{-- Описание --}}
                <div class="col-12">
                    <label class="fw-bold">Описание</label>
                    <textarea name="description" rows="4" class="form-control">{{ old('description', $clinic->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="modal-footer d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-primary px-5">Сохранить изменения</button>
        </div>
    </form>

    <form action="{{ route('clinics.destroy', $clinic->id) }}" method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить клинику?');" class="mt-2">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger">Удалить клинику</button>
    </form>

@else
    <div class="alert alert-info m-3 text-center">
        <h5>У вас пока нет созданной клиники</h5>
        <p>Вы можете зарегистрировать свой объект в разделе «Добавить объект».</p>
    </div>
@endif


