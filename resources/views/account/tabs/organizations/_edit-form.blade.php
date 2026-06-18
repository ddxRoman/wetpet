{{--
    Форма редактирования ОДНОЙ подтверждённой организации.
    Вызывается из organizations-profile.blade.php в цикле по каждой
    подтверждённой организации пользователя.

    Ожидает: $organization, $allCities, $groupedOrgFields
--}}

<form id="editOrganizationForm-{{ $organization->id }}"
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

    <div id="organizationErrors-{{ $organization->id }}" class="alert alert-danger d-none"></div>

    <div class="modal-body">
        <div class="row g-3">
            {{-- Название --}}
            <div class="col-md-8">
                <label class="fw-bold">Название организации</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $organization->name) }}" required>
            </div>

            {{-- Тип/Сфера --}}
            <div class="col-md-4">
                <label class="fw-bold">Сфера деятельности</label>
                <select name="type" class="form-select" required>
                    <option style="font-weight: 600;" value="">Выберите сферу:</option>
                    @foreach($groupedOrgFields as $groupName => $fields)
                        @foreach($fields as $field)
                            <option value="{{ $field->activity }}"
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
                <select name="city" class="form-select citySelect_organization" required>
                    @foreach($allCities as $city)
                        <option value="{{ $city->name }}"
                            {{ (old('city', $organization->city) == $city->name) ? 'selected' : '' }}>
                            {{ $city->name }} ({{ $city->region }})
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
                <input type="text" name="phone1" class="form-control" value="{{ old('phone1', $organization->phone1) }}">
            </div>
            <div class="col-md-6">
                <label class="fw-bold">Второй телефон</label>
                <input type="text" name="phone2" class="form-control" value="{{ old('phone2', $organization->phone2) }}">
            </div>
            <div class="col-md-6">
                <label class="fw-bold">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $organization->email) }}">
            </div>

            {{-- Мессенджеры --}}
            <div class="col-12 mt-2">
                <div class="accordion accordion-flush border-bottom" id="messengerAccordion-{{ $organization->id }}">
                    <div class="accordion-item" style="border: none;">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed text-primary fw-bold ps-0"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapseMessengers-{{ $organization->id }}"
                                    aria-expanded="false"
                                    style="background: none; box-shadow: none; font-size: 0.9rem;">
                                + Добавить мессенджеры
                            </button>
                        </h2>
                        <div id="collapseMessengers-{{ $organization->id }}"
                             class="accordion-collapse collapse"
                             data-bs-parent="#messengerAccordion-{{ $organization->id }}">
                            <div class="accordion-body px-0 py-3">
                                <div class="d-flex flex-column gap-3">

                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <img src="{{ Storage::url('icon/contacts/telegram.svg') }}" width="22">
                                        </span>
                                        <input type="text" name="telegram" class="form-control border-start-0"
                                               placeholder="Никнейм или телефон Telegram"
                                               value="{{ old('telegram', $organization->telegram ?? '') }}">
                                    </div>

                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <img src="{{ Storage::url('icon/contacts/whatsapp.svg') }}" width="22">
                                        </span>
                                        <input type="text" name="whatsapp" class="form-control border-start-0"
                                               placeholder="Номер телефона WhatsApp"
                                               value="{{ old('whatsapp', $organization->whatsapp ?? '') }}">
                                    </div>

                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <img src="{{ Storage::url('icon/contacts/max_messendger.svg') }}" width="22">
                                        </span>
                                        <input type="text" name="max" class="form-control border-start-0"
                                               placeholder="Данные Max Messenger"
                                               value="{{ old('max', $organization->max ?? '') }}">
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

            {{-- Расписание --}}
            <div class="col-md-6">
                <label class="fw-bold">График работы (часы)</label>
                <input type="text" name="schedule" class="form-control" value="{{ old('schedule', $organization->schedule) }}" placeholder="09:00-20:00">
            </div>
            <div class="col-md-6">
                <label class="fw-bold">Рабочие дни</label>
                <input type="text" name="workdays" class="form-control" value="{{ old('workdays', $organization->workdays) }}" placeholder="Пн-Пт">
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
        <button type="submit" class="btn btn-primary px-5">Сохранить изменения</button>
    </div>
</form>

<form action="{{ route('organizations.destroy', $organization->id) }}" method="POST"
      onsubmit="return confirm('Вы уверены, что хотите удалить организацию?');" class="mt-2">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-outline-danger">Удалить организацию</button>
</form>
