{{--
    Полная форма редактирования для Doctor / Specialist.
    Ожидает: $entity, $type ('doctor'|'specialist'), $entityId

    Покрывает ВСЕ поля из миграций doctors/specialists:
    name, slug (только Doctor), specialization, date_of_birth, city_id,
    clinic_id (Doctor) / organization_id (Specialist), experience,
    exotic_animals, On_site_assistance, photo, description,
    seo_title, seo_description
--}}

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
                <input type="text" name="specialization" class="form-control"
                       value="{{ old('specialization', $entity->specialization) }}"
                       placeholder="Например: Кардиолог" required>
            </div>

            @if($type === 'doctor')
                <div class="col-12">
                    <label class="form-label fw-medium">Адрес страницы (slug)</label>
                    <div class="input-group">
                        <span class="input-group-text">/doctors/</span>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $entity->slug) }}" required>
                    </div>
                </div>
            @endif
        </div>

        <hr class="my-4 opacity-25">

        {{-- ── Личные данные ── --}}
        <h6 class="fw-semibold mb-3">Личные данные</h6>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-medium">Дата рождения</label>
                <input type="date" name="date_of_birth" class="form-control"
                       value="{{ old('date_of_birth', optional($entity->date_of_birth)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Опыт работы (лет)</label>
                <input type="number" name="experience" class="form-control" min="0" max="70"
                       value="{{ old('experience', $entity->experience) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-medium">Город</label>
                <select name="city_id" class="form-select" required>
                    <option value="">— выберите город —</option>
                    @foreach(\App\Models\City::orderBy('name')->get() as $city)
                        <option value="{{ $city->id }}" {{ (old('city_id', $entity->city_id) == $city->id) ? 'selected' : '' }}>
                            {{ $city->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <hr class="my-4 opacity-25">

        {{-- ── Место работы ── --}}
        <h6 class="fw-semibold mb-3">Место работы</h6>
        <div class="row g-3">
            @if($type === 'doctor')
                <div class="col-12">
                    <label class="form-label fw-medium">Клиника</label>
                    <select name="clinic_id" class="form-select">
                        <option value="">— не выбрано (частная практика) —</option>
                        @foreach(\App\Models\Clinic::orderBy('name')->get() as $clinic)
                            <option value="{{ $clinic->id }}" {{ (old('clinic_id', $entity->clinic_id) == $clinic->id) ? 'selected' : '' }}>
                                {{ $clinic->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="col-12">
                    <label class="form-label fw-medium">Организация</label>
                    <select name="organization_id" class="form-select">
                        <option value="">— не выбрано (частная практика) —</option>
                        @foreach(\App\Models\Organization::orderBy('name')->get() as $org)
                            <option value="{{ $org->id }}" {{ (old('organization_id', $entity->organization_id) == $org->id) ? 'selected' : '' }}>
                                {{ $org->name }}
                            </option>
                        @endforeach
                    </select>
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
                <div class="form-text">JPG, PNG, WebP. Максимум 2 МБ.</div>
            </div>

            <div class="col-12">
                <label class="form-label fw-medium">Описание</label>
                <textarea name="description" class="form-control" rows="5">{{ old('description', $entity->description) }}</textarea>
            </div>
        </div>

        <hr class="my-4 opacity-25">

        {{-- ── SEO ── --}}
        <h6 class="fw-semibold mb-3">SEO</h6>
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
        </div>

        <hr class="my-4 opacity-25">

        <div class="text-end">
            <button type="submit" class="btn btn-primary px-5 rounded-pill">
                💾 Сохранить изменения
            </button>
        </div>
    </form>
</div>
