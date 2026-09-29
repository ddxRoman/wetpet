{{--
    Полная форма редактирования для Doctor / Specialist.
    Ожидает: $entity, $type ('doctor'|'specialist'), $entityId

    Покрывает ВСЕ поля из миграций doctors/specialists:
    name, slug (только Doctor), specialization, date_of_birth, city_id,
    clinic_id (Doctor) / organization_id (Specialist), experience,
    exotic_animals, On_site_assistance, photo, description,
    seo_title, seo_description
--}}

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

<div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
    <h5 class="fw-bold mb-4">📋 Основная информация</h5>

    <form method="POST" action="{{ route('owner.' . $type . '.update', $entityId) }}" enctype="multipart/form-data">
        @csrf

        {{-- ── Имя и специализация ── --}}
        <div class="row g-3">
            <div class="col-md-7">
                <label class="form-label fw-medium">Имя {{ $type === 'doctor' ? 'врача' : 'специалиста' }}</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $entity->name) }}" required>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-medium">Специализация</label>
                <select name="specialization" class="form-select" required>
                    <option value="">— выберите специализацию —</option>
                    @php
                        // У врачей (type=doctor) — только направления с activity=doctor,
                        // у специалистов (не врачей) — все остальные направления type=specialist.
                        $specializationOptions = \App\Models\FieldOfActivity::where('type', 'specialist')
                            ->when($type === 'doctor',
                                fn ($q) => $q->where('activity', 'doctor'),
                                fn ($q) => $q->where('activity', '!=', 'doctor'))
                            ->orderBy('name')
                            ->pluck('name');
                    @endphp
                    @foreach($specializationOptions as $option)
                        <option value="{{ $option }}" {{ old('specialization', $entity->specialization) === $option ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- @if($type === 'doctor')
                <div class="col-12">
                    <label class="form-label fw-medium">Адрес страницы (slug)</label>
                    <div class="input-group">
                        <span class="input-group-text">/doctors/</span>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $entity->slug) }}" required>
                    </div>
                </div>
            @endif -->
        </div>

        <hr class="my-4 opacity-25">

        {{-- ── Личные данные ── --}}
        <h6 class="fw-semibold mb-3">Личные данные</h6>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-medium">Дата рождения</label>
                <input type="date" name="date_of_birth" class="form-control" max="{{ now()->subYears(16)->format('Y-m-d') }}"
                       value="{{ old('date_of_birth', optional($entity->date_of_birth)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Начало практики (год и месяц)</label>
                <input type="month" name="practice_started_at" class="form-control" min="1950-01" max="{{ now()->format('Y-m') }}"
                       value="{{ old('practice_started_at', optional($entity->practice_started_at)->format('Y-m')) }}">
                <div class="form-text">Укажите год и месяц начала практики, и по этим данным будет рассчитан стаж</div>
            </div>
            @php
                $currentCityId = old('city_id', $entity->city_id);
                $currentCity   = $currentCityId ? \App\Models\City::find($currentCityId) : null;
                $currentRegion = $currentCity->region ?? '';
            @endphp
            <div class="col-md-4">
                <label class="form-label fw-medium">Регион</label>
                <select id="owner-specialist-region-select" class="form-select" data-current="{{ $currentRegion }}">
                    @if(!empty($currentRegion))
                        <option value="{{ $currentRegion }}" selected>{{ $currentRegion }}</option>
                    @endif
                </select>
                <div class="form-text">Только для фильтра городов, отдельно не сохраняется.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Город</label>
                <select name="city_id" id="owner-specialist-city-select" class="form-select" data-current="{{ $currentCityId }}" required>
                    <option value="">— выберите город —</option>
                    @if($currentCity)
                        <option value="{{ $currentCity->id }}" selected>{{ $currentCity->name }}</option>
                    @endif
                </select>
            </div>
        </div>

        <hr class="my-4 opacity-25">

        {{-- ── Место работы ── --}}
        <h6 class="fw-semibold mb-3">Место работы</h6>
        <div class="row g-3">
            @if($type === 'doctor')
                @php
                    $currentClinicId = old('clinic_id', $entity->clinic_id);
                    $currentClinic   = $currentClinicId ? \App\Models\Clinic::find($currentClinicId) : null;
                @endphp
                <div class="col-12">
                    <label class="form-label fw-medium">Клиника</label>
                    <select name="clinic_id" id="owner-doctor-workplace-select" class="form-select" data-current="{{ $currentClinicId }}">
                        <option value="">— не выбрано (частная практика) —</option>
                        @if($currentClinic)
                            <option value="{{ $currentClinic->id }}" selected>{{ $currentClinic->name }}</option>
                        @endif
                    </select>
                    <div class="form-text">Список зависит от выбранного города.</div>
                </div>
            @else
                @php
                    $currentOrganizationId = old('organization_id', $entity->organization_id);
                    $currentOrganization   = $currentOrganizationId ? \App\Models\Organization::find($currentOrganizationId) : null;
                @endphp
                <div class="col-12">
                    <label class="form-label fw-medium">Организация</label>
                    <select name="organization_id" id="owner-specialist-workplace-select" class="form-select" data-current="{{ $currentOrganizationId }}">
                        <option value="">— не выбрано (частная практика) —</option>
                        @if($currentOrganization)
                            <option value="{{ $currentOrganization->id }}" selected>{{ $currentOrganization->name }}</option>
                        @endif
                    </select>
                    <div class="form-text">Список зависит от выбранного города.</div>
                </div>
            @endif
        </div>

        <hr class="my-4 opacity-25">

        {{-- ── Дополнительные параметры ── --}}
        <h6 class="fw-semibold mb-3">Дополнительные параметры</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="exotic_animals" id="exotic_animals_{{ $entityId }}"
                           value="1" {{ old('exotic_animals', $entity->exotic_animals) ? 'checked' : '' }}>
                    <label class="form-check-label" for="exotic_animals_{{ $entityId }}">
                        Работает с экзотическими животными
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="On_site_assistance" id="on_site_{{ $entityId }}"
                           value="1" {{ old('On_site_assistance', $entity->On_site_assistance) ? 'checked' : '' }}>
                    <label class="form-check-label" for="on_site_{{ $entityId }}">
                        Выезд на дом
                    </label>
                </div>
            </div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="works_online" id="works_online_{{ $entityId }}"
                           value="1" {{ old('works_online', $entity->works_online) ? 'checked' : '' }}>
                    <label class="form-check-label" for="works_online_{{ $entityId }}">
                        💻 Работает онлайн
                    </label>
                    <div class="form-text">Карточка получит отметку «Онлайн», попадёт в фильтр «Онлайн» и будет показываться во всех городах. Регион и город указываются в любом случае. Если работаете только онлайн, организацию и адрес можно не указывать.</div>
                </div>
            </div>
        </div>

        <hr class="my-4 opacity-25">

        {{-- ── Фото и описание ── --}}
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-medium">Фото профиля</label>
                <div class="d-flex align-items-center gap-3">
                    @if(!empty($entity->photo))
                        <img src="{{ Storage::url($entity->photo) }}" style="width:80px;height:80px;border-radius:50%;object-fit:cover;">
                    @endif
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>
                @if(!empty($entity->photo))
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="remove_photo" id="remove-photo" value="1">
                        <label class="form-check-label text-danger" for="remove-photo">Удалить фото профиля</label>
                    </div>
                @endif
                <div class="form-text">JPG, PNG, WebP. Максимум 2 МБ.</div>
            </div>

            <div class="col-12">
                <label class="form-label fw-medium">Описание</label>
                <textarea name="description" class="form-control" rows="5">{{ old('description', $entity->description) }}</textarea>
            </div>
        </div>

        <!-- <hr class="my-4 opacity-25"> -->

        {{-- ── SEO ── --}}
        <!-- <h6 class="fw-semibold mb-3">SEO</h6>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-medium">SEO заголовок (title)</label>
                <input type="text" name="seo_title" class="form-control" maxlength="255"
                       value="{{ old('seo_title', $entity->seo_title) }}"
                       placeholder="Если не заполнено, используется имя">
            </div>
            <div class="col-12">
                <label class="form-label fw-medium">SEO описание (description)</label>
                <textarea name="seo_description" class="form-control" rows="3" maxlength="320">{{ old('seo_description', $entity->seo_description) }}</textarea>
            </div>
        </div> -->

        <hr class="my-4 opacity-25">

        <div class="text-end">
            <button type="submit" class="btn btn-primary px-5 rounded-pill">
                💾 Сохранить изменения
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var regionSelect     = document.getElementById('owner-specialist-region-select');
    var citySelect       = document.getElementById('owner-specialist-city-select');
    var workplaceSelect  = document.getElementById('owner-doctor-workplace-select')
        || document.getElementById('owner-specialist-workplace-select');
    if (!regionSelect || !citySelect) return;

    var isDoctor    = !!document.getElementById('owner-doctor-workplace-select');
    var hasSelect2  = !!(window.jQuery && typeof window.jQuery.fn.select2 === 'function');

    function initSelect2(select, placeholder) {
        if (!hasSelect2 || !select) return;
        window.jQuery(select).select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: placeholder,
            allowClear: true,
            language: {
                noResults: function () { return 'Ничего не найдено'; },
                searching: function () { return 'Поиск…'; }
            }
        });
    }

    // values: массив строк (регион) или массив {id, name} (город/клиника/организация)
    function fillOptions(select, values, selectedValue, isIdBased) {
        if (!select) return;
        var fallbackOption = select.querySelector('option[selected]');
        var fallbackLabel  = fallbackOption ? fallbackOption.textContent : selectedValue;

        select.innerHTML = '';
        select.appendChild(new Option('', '', false, false));

        var found = false;
        values.forEach(function (item) {
            var value = isIdBased ? item.id : item;
            var label = isIdBased ? item.name : item;
            var isSelected = selectedValue !== null && selectedValue !== undefined && selectedValue !== '' && String(value) === String(selectedValue);
            if (isSelected) found = true;
            select.appendChild(new Option(label, value, isSelected, isSelected));
        });

        if (selectedValue !== null && selectedValue !== undefined && selectedValue !== '' && !found) {
            select.appendChild(new Option(fallbackLabel, selectedValue, true, true));
        }

        if (hasSelect2) window.jQuery(select).trigger('change.select2');
    }

    var currentRegion       = regionSelect.dataset.current || '';
    var currentCityId       = citySelect.dataset.current || '';
    var currentWorkplaceId  = workplaceSelect ? (workplaceSelect.dataset.current || '') : '';

    initSelect2(regionSelect, 'Начните вводить регион...');
    initSelect2(citySelect, 'Начните вводить город...');
    initSelect2(workplaceSelect, isDoctor ? 'Начните вводить клинику...' : 'Начните вводить организацию...');

    function loadWorkplaces(cityId, selectedId) {
        if (!workplaceSelect) return;
        if (!cityId) {
            fillOptions(workplaceSelect, [], selectedId, true);
            return;
        }
        var url = isDoctor
            ? '/api/clinics/by-city/' + encodeURIComponent(cityId)
            : '/get-organizations-by-city-id/' + encodeURIComponent(cityId);

        fetch(url)
            .then(function (res) { return res.json(); })
            .then(function (list) {
                fillOptions(workplaceSelect, Array.isArray(list) ? list : [], selectedId, true);
            })
            .catch(function (err) { console.error('Не удалось загрузить список по городу:', err); });
    }

    function loadCities(region, selectedCityId) {
        if (!region) {
            fillOptions(citySelect, [], selectedCityId, true);
            loadWorkplaces(null, null);
            return;
        }
        fetch('/api/cities/by-region/' + encodeURIComponent(region))
            .then(function (res) { return res.json(); })
            .then(function (cities) {
                fillOptions(citySelect, Array.isArray(cities) ? cities : [], selectedCityId, true);
                loadWorkplaces(selectedCityId || citySelect.value, currentWorkplaceId);
            })
            .catch(function (err) { console.error('Не удалось загрузить города региона:', err); });
    }

    fetch('/api/regions')
        .then(function (res) { return res.json(); })
        .then(function (regions) {
            fillOptions(regionSelect, Array.isArray(regions) ? regions : [], currentRegion, false);
            loadCities(currentRegion, currentCityId);
        })
        .catch(function (err) { console.error('Не удалось загрузить список регионов:', err); });

    regionSelect.addEventListener('change', function () {
        loadCities(regionSelect.value, null);
    });

    citySelect.addEventListener('change', function () {
        loadWorkplaces(citySelect.value, null);
    });
});
</script>
