
<div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
    <h5 class="fw-bold mb-4">📋 Основная информация</h5>

    <form method="POST" action="{{ route('owner.' . $type . '.update', $entityId) }}" enctype="multipart/form-data">
        @csrf

        {{-- ── Название и сфера деятельности ── --}}
        <div class="row g-3">
            <div class="col-md-{{ $type === 'organization' ? '8' : '12' }}">
                <label class="form-label fw-medium">Название {{ $type === 'organization' ? 'организации' : 'клиники' }}</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $entity->name) }}" required>
            </div>

            @if($type === 'organization')
                <div class="col-md-4">
                    <label class="form-label fw-medium">Сфера деятельности</label>dasdsadweqweqw
                    <select name="field_of_activity_id" class="form-select">
                        <option value="">— не выбрано —</option>
                        @foreach(\App\Models\FieldOfActivity::where('type', 'organization')->orderBy('name')->get() as $field)
                            <option value="{{ $field->id }}" {{ (old('field_of_activity_id', $entity->field_of_activity_id) == $field->id) ? 'selected' : '' }}>
                                {{ $field->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- ── Slug ── --}}
            <!-- <div class="col-12">
                <label class="form-label fw-medium">Адрес страницы (slug)</label>
                <div class="input-group">
                    <span class="input-group-text">/{{ $type }}s/</span>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $entity->slug) }}" required>
                </div>
                <div class="form-text">Латиница, цифры и дефисы. Используется в публичной ссылке.</div>
            </div> -->
        </div>

        <hr class="my-4 opacity-25">

        {{-- ── Адрес ── --}}
        <h6 class="fw-semibold mb-3">Адрес</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-medium">Регион</label>
                <select name="region" id="ownerOrgRegionSelect" class="form-select">
                    <option value="">Выберите регион</option>
                    @foreach($allCities->pluck('region')->unique()->sort() as $regionName)
                        <option value="{{ $regionName }}" {{ old('region', $entity->region) === $regionName ? 'selected' : '' }}>
                            {{ $regionName }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">Город</label>
                <select name="city" id="ownerOrgCitySelect" class="form-select" required>
                    @if($entity->city)
                        <option value="{{ $entity->city }}" selected>{{ $entity->city }}</option>
                    @else
                        <option value="">Сначала выберите регион</option>
                    @endif
                </select>
            </div>

            <script>
            (function () {
                const regionSelect = document.getElementById('ownerOrgRegionSelect');
                const citySelect   = document.getElementById('ownerOrgCitySelect');
                const currentCity  = @json($entity->city);
                let $region, $city;

                function initSelect2() {
                    $region = $('#ownerOrgRegionSelect').select2({ placeholder: 'Поиск региона...', width: '100%' });
                    $city   = $('#ownerOrgCitySelect').select2({ placeholder: 'Поиск города...', width: '100%' });

                    $region.on('change', () => loadCities($region.val(), null));

                    // При открытии формы редактирования сразу подгружаем города текущего
                    // региона, сохраняя уже выбранный город.
                    if (regionSelect.value) {
                        loadCities(regionSelect.value, currentCity);
                    }
                }

                function loadCities(region, preselect) {
                    citySelect.innerHTML = '<option value="">Загрузка...</option>';
                    $city.trigger('change.select2');

                    if (!region) {
                        citySelect.innerHTML = '<option value="">Сначала выберите регион</option>';
                        $city.trigger('change.select2');
                        return;
                    }

                    fetch(`/api/cities/by-region/${encodeURIComponent(region)}`)
                        .then(r => r.json())
                        .then(list => {
                            citySelect.innerHTML = '<option value="">Выберите город</option>';
                            list.forEach(c => {
                                const opt = document.createElement('option');
                                opt.value = c.name;
                                opt.textContent = c.name;
                                if (preselect && c.name === preselect) opt.selected = true;
                                citySelect.appendChild(opt);
                            });
                            $city.trigger('change.select2');
                        })
                        .catch(() => {
                            citySelect.innerHTML = '<option value="">Ошибка загрузки</option>';
                            $city.trigger('change.select2');
                        });
                }

                if (window.jQuery && $.fn.select2) {
                    initSelect2();
                } else {
                    document.addEventListener('DOMContentLoaded', initSelect2);
                }
            })();
            </script>

            <div class="col-md-8">
                <label class="form-label fw-medium">Улица</label>
                <input type="text" name="street" class="form-control" value="{{ old('street', $entity->street) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Дом</label>
                <input type="text" name="house" class="form-control" value="{{ old('house', $entity->house) }}">
            </div>

            <div class="col-12">
                <label class="form-label fw-medium">Комментарий к адресу</label>
                <input type="text" name="address_comment" class="form-control"
                       value="{{ old('address_comment', $entity->address_comment) }}"
                       placeholder="Вход со двора, 2 этаж...">
            </div>
        </div>

        <hr class="my-4 opacity-25">

        {{-- ── Контакты ── --}}
        <h6 class="fw-semibold mb-3">Контакты</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-medium">Телефон 1</label>
                <input type="text" name="phone1" class="form-control" value="{{ old('phone1', $entity->phone1) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">Телефон 2</label>
                <input type="text" name="phone2" class="form-control" value="{{ old('phone2', $entity->phone2) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-medium">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $entity->email) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">Сайт</label>
                <input type="url" name="website" class="form-control" value="{{ old('website', $entity->website) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-medium">Telegram</label>
                <input type="text" name="telegram" class="form-control" value="{{ old('telegram', $entity->telegram) }}" placeholder="@username">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">WhatsApp</label>
                <input type="text" name="whatsapp" class="form-control" value="{{ old('whatsapp', $entity->whatsapp) }}">
            </div>
        </div>

        <hr class="my-4 opacity-25">

        {{-- ── График работы ── --}}
        <h6 class="fw-semibold mb-3">График работы</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-medium">Часы работы</label>
                <input type="text" name="schedule" class="form-control" value="{{ old('schedule', $entity->schedule) }}" placeholder="09:00–20:00">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">Рабочие дни</label>
                <input type="text" name="workdays" class="form-control" value="{{ old('workdays', $entity->workdays) }}" placeholder="Пн–Вс">
            </div>
        </div>

        <hr class="my-4 opacity-25">

        {{-- ── Логотип и описание ── --}}
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-medium">Логотип</label>
                <div class="d-flex align-items-center gap-3">
                    @if(!empty($entity->logo))
                        <img src="{{ Storage::url($entity->logo) }}" style="width:80px;height:80px;border-radius:8px;object-fit:cover;">
                    @endif
                    <input type="file" name="logo" class="form-control" accept="image/*">
                </div>
                <div class="form-text">JPG, PNG, WebP. Максимум 2 МБ.</div>
            </div>

            <div class="col-12">
                <label class="form-label fw-medium">Описание</label>
                <textarea name="description" class="form-control" rows="5">{{ old('description', $entity->description) }}</textarea>
            </div>
        </div>

        <!-- <hr class="my-4 opacity-25"> -->

        <!-- {{-- ── SEO ── --}}
        <h6 class="fw-semibold mb-3">SEO</h6>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-medium">SEO заголовок (title)</label>
                <input type="text" name="seo_title" class="form-control" maxlength="255"
                       value="{{ old('seo_title', $entity->seo_title) }}"
                       placeholder="Если не заполнено, используется название">
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
