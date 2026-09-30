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
                       value="{{ old('date_of_birth', optional(\Illuminate\Support\Carbon::make($entity->date_of_birth))->format('Y-m-d')) }}">
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

        {{-- ── Места работы (можно выбрать несколько) ── --}}
        <h6 class="fw-semibold mb-3">Места работы</h6>
        <div class="row g-3">
            @php
                $isDoctorForm = $type === 'doctor';
                $workplaceRelation = $isDoctorForm ? 'clinics' : 'organizations';
                $workplaceField    = $isDoctorForm ? 'clinic_ids' : 'organization_ids';
                $currentWorkplaces = $entity->{$workplaceRelation};
                $selectedIds = collect(old($workplaceField, $currentWorkplaces->pluck('id')->all()))->map(fn ($v) => (int) $v)->all();
                $selectedModels = $currentWorkplaces->whereIn('id', $selectedIds);
                // Выбранные, но ещё не сохранённые (после ошибки валидации) места работы
                $missingIds = array_diff($selectedIds, $selectedModels->pluck('id')->all());
                if ($missingIds) {
                    $extra = $isDoctorForm
                        ? \App\Models\Clinic::whereIn('id', $missingIds)->get()
                        : \App\Models\Organization::whereIn('id', $missingIds)->get();
                    $selectedModels = $selectedModels->concat($extra);
                }
            @endphp
            @php
                // Основное место работы (по нему формируется адрес страницы) показываем первым
                $primaryWorkplaceId = (int) ($isDoctorForm ? $entity->clinic_id : $entity->organization_id);
                $selectedModels = $selectedModels->sortByDesc(fn ($m) => (int) $m->id === $primaryWorkplaceId)->values();
            @endphp
            <div class="col-12">
                <label class="form-label fw-medium">{{ $isDoctorForm ? 'Клиники' : 'Организации' }}</label>

                {{-- Мультивыбор на чистом JS (не зависит от select2): выбранные места — «чипсы», поиск — по всем городам --}}
                <div id="owner-workplaces" class="position-relative"
                     data-type="{{ $workplaceRelation }}"
                     data-field="{{ $workplaceField }}"
                     data-placeholder="{{ $isDoctorForm ? 'Начните вводить название клиники…' : 'Начните вводить название организации…' }}">
                    <div class="form-control d-flex flex-wrap align-items-center gap-2 h-auto" data-role="box" style="min-height:44px;cursor:text;">
                        <span class="d-contents" data-role="chips">
                            @foreach($selectedModels as $place)
                                <span class="badge rounded-pill text-bg-light border d-inline-flex align-items-center gap-2 py-2 px-3 fw-normal text-wrap text-start" data-chip>
                                    <span data-role="label">{{ $place->name }}{{ $place->city ? ' — ' . $place->city : '' }}{{ $place->street ? ', ' . trim($place->street . ' ' . $place->house) : '' }}</span>
                                    <span class="text-primary small d-none" data-primary>основное</span>
                                    <button type="button" class="btn-close" style="font-size:.6rem;" aria-label="Убрать" data-remove></button>
                                    <input type="hidden" name="{{ $workplaceField }}[]" value="{{ $place->id }}">
                                </span>
                            @endforeach
                        </span>
                        <input type="text" data-role="input" autocomplete="off" class="border-0 flex-grow-1 bg-transparent"
                               style="min-width:220px;outline:none;"
                               placeholder="{{ $isDoctorForm ? 'Начните вводить название клиники…' : 'Начните вводить название организации…' }}">
                    </div>
                    <div class="list-group position-absolute w-100 shadow d-none" data-role="dropdown"
                         style="z-index:1050;max-height:260px;overflow:auto;top:100%;"></div>
                </div>

                <div class="form-text">
                    Можно указать несколько мест работы, в том числе в разных городах: начните вводить название и выберите из списка.
                    Если не выбрано ничего — частная практика. Первое место в списке считается основным.
                </div>
            </div>
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
    if (!regionSelect || !citySelect) return;

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

    initSelect2(regionSelect, 'Начните вводить регион...');
    initSelect2(citySelect, 'Начните вводить город...');

    function loadCities(region, selectedCityId) {
        if (!region) {
            fillOptions(citySelect, [], selectedCityId, true);
            return;
        }
        fetch('/api/cities/by-region/' + encodeURIComponent(region))
            .then(function (res) { return res.json(); })
            .then(function (cities) {
                fillOptions(citySelect, Array.isArray(cities) ? cities : [], selectedCityId, true);
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

});
</script>

<script>
// Мультивыбор мест работы: работает без select2/jQuery.
(function () {
    var root = document.getElementById('owner-workplaces');
    if (!root) return;

    var type     = root.dataset.type;   // clinics | organizations
    var field    = root.dataset.field;  // clinic_ids | organization_ids
    var box      = root.querySelector('[data-role="box"]');
    var chips    = root.querySelector('[data-role="chips"]');
    var input    = root.querySelector('[data-role="input"]');
    var dropdown = root.querySelector('[data-role="dropdown"]');
    var cityEl   = document.getElementById('owner-specialist-city-select');

    var timer = null;
    var requestId = 0;

    function selectedIds() {
        return Array.prototype.map.call(
            chips.querySelectorAll('input[type="hidden"]'),
            function (i) { return String(i.value); }
        );
    }

    function refreshPrimary() {
        var items = chips.querySelectorAll('[data-chip]');
        Array.prototype.forEach.call(items, function (chip, index) {
            chip.querySelector('[data-primary]').classList.toggle('d-none', !(index === 0 && items.length > 1));
        });
    }

    function addChip(id, label) {
        if (selectedIds().indexOf(String(id)) !== -1) return;

        var chip = document.createElement('span');
        chip.className = 'badge rounded-pill text-bg-light border d-inline-flex align-items-center gap-2 py-2 px-3 fw-normal text-wrap text-start';
        chip.setAttribute('data-chip', '');

        var text = document.createElement('span');
        text.textContent = label;

        var primary = document.createElement('span');
        primary.className = 'text-primary small d-none';
        primary.setAttribute('data-primary', '');
        primary.textContent = 'основное';

        var remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'btn-close';
        remove.style.fontSize = '.6rem';
        remove.setAttribute('aria-label', 'Убрать');
        remove.setAttribute('data-remove', '');

        var hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = field + '[]';
        hidden.value = id;

        chip.appendChild(text);
        chip.appendChild(primary);
        chip.appendChild(remove);
        chip.appendChild(hidden);
        chips.appendChild(chip);
        refreshPrimary();
    }

    function closeDropdown() {
        dropdown.classList.add('d-none');
        dropdown.innerHTML = '';
    }

    function renderResults(results) {
        var chosen = selectedIds();
        var list = results.filter(function (r) { return chosen.indexOf(String(r.id)) === -1; });

        dropdown.innerHTML = '';
        if (!list.length) {
            var empty = document.createElement('div');
            empty.className = 'list-group-item text-muted small';
            empty.textContent = 'Ничего не найдено';
            dropdown.appendChild(empty);
        } else {
            list.forEach(function (r) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action';
                btn.textContent = r.text;
                btn.addEventListener('mousedown', function (e) {
                    e.preventDefault(); // не терять фокус поля до добавления
                    addChip(r.id, r.text);
                    input.value = '';
                    closeDropdown();
                    input.focus();
                });
                dropdown.appendChild(btn);
            });
        }
        dropdown.classList.remove('d-none');
    }

    function search() {
        var current = ++requestId;
        var params = new URLSearchParams({ q: input.value.trim(), city_id: cityEl ? cityEl.value : '' });

        fetch('/api/workplaces/' + type + '?' + params.toString(), { headers: { 'Accept': 'application/json' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (current !== requestId) return; // пришёл устаревший ответ
                renderResults(Array.isArray(data.results) ? data.results : []);
            })
            .catch(function (err) { console.error('Не удалось загрузить список мест работы:', err); });
    }

    input.addEventListener('focus', search);
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(search, 250);
    });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') e.preventDefault();       // Enter не отправляет форму
        if (e.key === 'Escape') closeDropdown();
        if (e.key === 'Backspace' && input.value === '') {
            var last = chips.querySelector('[data-chip]:last-child');
            if (last) { last.remove(); refreshPrimary(); }
        }
    });

    box.addEventListener('click', function (e) {
        if (e.target.closest('[data-remove]')) {
            e.target.closest('[data-chip]').remove();
            refreshPrimary();
            return;
        }
        input.focus();
    });

    document.addEventListener('click', function (e) {
        if (!root.contains(e.target)) closeDropdown();
    });

    refreshPrimary();
})();
</script>
