@vite(['resources/js/pages/edit_organizations.js'])

@if(isset($organization) && $organization)
    <form id="editOrganizationForm"
          method="POST"
          action="{{ route('organizations-profile.update', $organization->id) }}"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
{{-- Верхняя панель с заголовком и кнопкой перехода для организации --}}
<div class="d-flex btn-eye justify-content-between align-items-center mb-4 px-3">
    <h4 class="modal-title mb-0">Редактирование организации</h4>
    
    <div class="d-flex gap-2">
        @if($organization->slug)
            <a href="{{ route('organizations.show', $organization->slug) }}" 
               target="_blank" 
               class="btn-view-profile d-flex align-items-center justify-content-center shadow-sm"
               title="Перейти к карточке организации">
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

        <div id="organizationErrors" class="alert alert-danger d-none"></div>

        <div class="modal-body">
            <div class="row g-3">
                {{-- Название --}}
                <div class="col-md-8">
                    <label class="fw-bold">Название организации</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $organization->name) }}" required>
                </div>

                {{-- Тип/Сфера (из твоего контроллера это поле type) --}}
                <div class="col-md-4">
                    <label class="fw-bold">Сфера деятельности</label>
                    <select name="type" class="form-select" required>
                        <option style="font-weight: 600;" value="">Выберите сферу:</option>
                        @foreach($groupedOrgFields as $groupName => $fields)
                            
                                @foreach($fields as $field)
                                    <option  value="{{ $field->activity }}" 
                                        {{ (old('type', $organization->type) == $field->activity) ? 'selected' : '' }}>
                                        &#10148; {{ $field->name }}
                                    </option>
                                @endforeach

                        @endforeach
                    </select>
                </div>

                
                {{-- Город --}}
                <div class="col-md-6">
                    <label class="fw-bold">Город</label>
                    <select name="city" id="citySelect_organization" class="form-select" required>
                        @foreach($allCities as $city)
                            <option value="{{ $city->name }}" 
                                {{ (old('city', $organization->city) == $city->name) ? 'selected' : '' }}>
                                {{ $city->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Адрес --}}
                <div class="col-md-4">
                    <label class="fw-bold">Улица</label>
                    <input type="text" name="street" class="form-control" value="{{ old('street', $organization->street) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="fw-bold">Дом</label>
                    <input type="text" name="house" class="form-control" value="{{ old('house', $organization->house) }}" required>
                </div>

                {{-- Контакты --}}
                <div class="col-md-6">
                    <label class="fw-bold">Телефон</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $organization->phone) }}">
                </div>
                <div class="col-md-6">
                    <label class="fw-bold">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $organization->email) }}">
                </div>

                {{-- Расписание --}}
                <div class="col-12">
                    <label class="fw-bold">График работы</label>
                    <input type="text" name="schedule" class="form-control" value="{{ old('schedule', $organization->schedule) }}" placeholder="Например: Пн-Пт 09:00-20:00">
                </div>

                {{-- Логотип --}}
                <div class="col-12 mt-3">
                    <label class="fw-bold mb-2">Логотип</label>
                    <div class="d-flex align-items-center gap-3">
                        @if($organization->logo)
                            <img src="{{ Storage::url($organization->logo) }}" alt="Logo" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                        @endif
                        <input type="file" name="logo" class="form-control" accept="image/*">
                    </div>
                </div>

                {{-- Описание --}}
                <div class="col-12">
                    <label class="fw-bold">Описание</label>
                    <textarea name="description" rows="4" class="form-control">{{ old('description', $organization->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="modal-footer d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-outline-danger" 
                    onclick="if(confirm('Удалить организацию?')) document.getElementById('delete-org-form').submit();">
                Удалить организацию
            </button>
            <button type="submit" class="btn btn-primary px-5">Сохранить изменения</button>
        </div>
    </form>

    {{-- Скрытая форма удаления --}}
    <form id="delete-org-form" action="{{ route('organizations-profile.destroy', $organization->id) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

@else
    <div class="alert alert-info m-3 text-center">
        <h5>У вас пока нет созданной организации</h5>
        <p>Вы можете зарегистрировать свой объект в разделе «Добавить объект».</p>
    </div>
@endif