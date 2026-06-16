{{--
    Общие вкладки для всех типов кабинетов.
    Подключать в clinic.blade.php, organization.blade.php и т.д.:
    @include('pages.owner._tabs', ['entity' => $clinic, 'type' => 'clinic', 'entityId' => $clinic->id])
--}}

@php $activeTab = request('tab', 'info'); @endphp

{{-- ════════════════ ВКЛАДКА: ОСНОВНАЯ ИНФОРМАЦИЯ ════════════════ --}}
@if($activeTab === 'info')
<div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
    <h5 class="fw-bold mb-4">📋 Основная информация</h5>

    <form method="POST" action="{{ route('owner.' . $type . '.update', $entityId) }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-medium">Название</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $entity->name) }}" required>
            </div>

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

            <div class="col-12">
                <label class="form-label fw-medium">Режим работы</label>
                <input type="text" name="schedule" class="form-control" value="{{ old('schedule', $entity->schedule) }}" placeholder="Пн–Пт 9:00–19:00">
            </div>

            <div class="col-12">
                <label class="form-label fw-medium">Описание</label>
                <textarea name="description" class="form-control" rows="5">{{ old('description', $entity->description) }}</textarea>
            </div>

            <div class="col-12">
                <label class="form-label fw-medium">Комментарий к адресу</label>
                <input type="text" name="address_comment" class="form-control"
                       value="{{ old('address_comment', $entity->address_comment) }}"
                       placeholder="Вход со двора, 2 этаж...">
            </div>

            {{-- Логотип/фото --}}
            <div class="col-12">
                <label class="form-label fw-medium">
                    {{ in_array($type, ['doctor', 'specialist']) ? 'Фото профиля' : 'Логотип' }}
                </label>
                @php $photoField = in_array($type, ['doctor', 'specialist']) ? 'photo' : 'logo'; @endphp
                @if(!empty($entity->$photoField))
                    <div class="mb-2">
                        <img src="{{ Storage::url($entity->$photoField) }}"
                             style="height:80px;border-radius:8px;object-fit:cover;">
                    </div>
                @endif
                <input type="file" name="{{ $photoField }}" class="form-control" accept="image/*">
                <div class="form-text">JPG, PNG, WebP. Максимум 2 МБ.</div>
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
@endif

{{-- ════════════════ ВКЛАДКА: ФОТОГРАФИИ ════════════════ --}}
@if($activeTab === 'photos')
<div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
    <h5 class="fw-bold mb-4">📷 Фотогалерея</h5>

    {{-- Загрузка нового фото --}}
    <div class="border rounded-3 p-4 mb-4 bg-light">
        <h6 class="fw-semibold mb-3">Добавить фото</h6>
        <div class="row g-3">
            <div class="col-md-7">
                <input type="file" id="photo-upload-input" class="form-control" accept="image/*" multiple>
            </div>
            <div class="col-md-5">
                <input type="text" id="photo-caption-input" class="form-control" placeholder="Подпись (необязательно)">
            </div>
        </div>
        <button class="btn btn-primary mt-3 rounded-pill px-4" id="photo-upload-btn">
            ⬆️ Загрузить
        </button>
        <div id="photo-upload-status" class="mt-2 text-muted small"></div>
    </div>

    {{-- Сетка фото --}}
    <div class="row g-3" id="photos-grid">
        @forelse($photos as $photo)
            <div class="col-6 col-md-4 col-lg-3 photo-card" id="photo-{{ $photo->id }}">
                <div class="position-relative rounded-3 overflow-hidden" style="aspect-ratio:1;">
                    <img src="{{ Storage::url($photo->path) }}"
                         class="w-100 h-100" style="object-fit:cover;">
                    <button class="btn btn-danger btn-sm btn-delete-photo position-absolute top-0 end-0 m-1 rounded-circle"
                            data-id="{{ $photo->id }}"
                            style="width:28px;height:28px;padding:0;font-size:14px;">
                        ×
                    </button>
                </div>
                @if($photo->caption)
                    <div class="text-muted mt-1" style="font-size:12px;">{{ $photo->caption }}</div>
                @endif
            </div>
        @empty
            <div class="col-12 text-center text-muted py-4" id="no-photos-msg">
                Фотографий пока нет. Добавьте первое фото!
            </div>
        @endforelse
    </div>
</div>

<script>
document.getElementById('photo-upload-btn')?.addEventListener('click', function () {
    const files   = document.getElementById('photo-upload-input').files;
    const caption = document.getElementById('photo-caption-input').value;
    const status  = document.getElementById('photo-upload-status');

    if (!files.length) { status.textContent = 'Выберите файл'; return; }

    status.textContent = 'Загрузка…';

    Array.from(files).forEach(file => {
        const fd = new FormData();
        fd.append('photo',       file);
        fd.append('caption',     caption);
        fd.append('entity_type', '{{ $type }}');
        fd.append('entity_id',   '{{ $entityId }}');
        fd.append('_token',      document.querySelector('meta[name="csrf-token"]').content);

        fetch('/owner/photos/upload', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('no-photos-msg')?.remove();
                    const grid = document.getElementById('photos-grid');
                    grid.insertAdjacentHTML('beforeend', `
                        <div class="col-6 col-md-4 col-lg-3 photo-card" id="photo-${data.photo.id}">
                            <div class="position-relative rounded-3 overflow-hidden" style="aspect-ratio:1;">
                                <img src="${data.photo.url}" class="w-100 h-100" style="object-fit:cover;">
                                <button class="btn btn-danger btn-sm btn-delete-photo position-absolute top-0 end-0 m-1 rounded-circle"
                                        data-id="${data.photo.id}"
                                        style="width:28px;height:28px;padding:0;font-size:14px;"
                                        onclick="deletePhoto(this)">
                                    ×
                                </button>
                            </div>
                            ${data.photo.caption ? `<div class="text-muted mt-1" style="font-size:12px;">${data.photo.caption}</div>` : ''}
                        </div>
                    `);
                    status.textContent = 'Фото загружено ✓';
                }
            })
            .catch(() => { status.textContent = 'Ошибка загрузки'; });
    });
});

function deletePhoto(btn) {
    if (!confirm('Удалить фото?')) return;
    fetch(`/owner/photos/${btn.dataset.id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    }).then(r => r.json()).then(d => {
        if (d.success) btn.closest('.photo-card')?.remove();
    });
}
</script>
@endif

{{-- ════════════════ ВКЛАДКА: УСЛУГИ И ЦЕНЫ ════════════════ --}}
@if($activeTab === 'services')
<div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
    <h5 class="fw-bold mb-4">💊 Услуги и цены</h5>

    {{-- Форма добавления --}}
    <div class="border rounded-3 p-4 mb-4 bg-light">
        <h6 class="fw-semibold mb-3">Добавить / обновить цену</h6>
        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label fw-medium small">Услуга</label>
                <select id="price-service" class="form-select">
                    <option value="">— Выберите услугу —</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-medium small">Цена</label>
                <input type="number" id="price-amount" class="form-control" placeholder="1500" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-medium small">Валюта</label>
                <select id="price-currency" class="form-select">
                    <option value="руб.">руб.</option>
                    <option value="₽">₽</option>
                    <option value="USD">USD</option>
                </select>
            </div>
        </div>
        <button class="btn btn-primary mt-3 rounded-pill px-4" id="price-save-btn">
            + Добавить
        </button>
        <div id="price-save-status" class="mt-2 text-muted small"></div>
    </div>

    {{-- Таблица текущих цен --}}
    <table class="table table-hover align-middle" id="prices-table">
        <thead class="table-light">
            <tr>
                <th>Услуга</th>
                <th>Цена</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($entity->prices ?? [] as $price)
                <tr id="price-row-{{ $price->id }}">
                    <td>{{ $price->service->name ?? '—' }}</td>
                    <td>{{ $price->price }} {{ $price->currency }}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-danger rounded-pill btn-delete-price"
                                data-id="{{ $price->id }}">
                            Удалить
                        </button>
                    </td>
                </tr>
            @empty
                <tr id="no-prices-row">
                    <td colspan="3" class="text-center text-muted py-3">Цены не добавлены</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
document.getElementById('price-save-btn')?.addEventListener('click', function () {
    const serviceId = document.getElementById('price-service').value;
    const price     = document.getElementById('price-amount').value;
    const currency  = document.getElementById('price-currency').value;
    const status    = document.getElementById('price-save-status');

    if (!serviceId || !price) { status.textContent = 'Выберите услугу и укажите цену'; return; }

    status.textContent = 'Сохранение…';

    fetch('/owner/prices/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            entity_type: '{{ $type }}',
            entity_id:   {{ $entityId }},
            service_id:  serviceId,
            price:       price,
            currency:    currency,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            status.textContent = '✓ Сохранено';
            window.location.reload();
        }
    })
    .catch(() => { status.textContent = 'Ошибка'; });
});
</script>
@endif

{{-- ════════════════ ВКЛАДКА: SEO ════════════════ --}}
@if($activeTab === 'seo')
<div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
    <h5 class="fw-bold mb-4">🔍 SEO настройки</h5>

    <form method="POST" action="{{ route('owner.' . $type . '.update', $entityId) }}">
        @csrf

        <div class="mb-4">
            <label class="form-label fw-medium">SEO Title</label>
            <input type="text" name="seo_title" class="form-control"
                   value="{{ old('seo_title', $entity->seo_title) }}"
                   maxlength="70"
                   placeholder="Автоматически: {{ $entity->name }}">
            <div class="form-text">Рекомендуется 50–70 символов</div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-medium">SEO Description</label>
            <textarea name="seo_description" class="form-control" rows="3"
                      maxlength="320"
                      placeholder="Краткое описание для поисковых систем...">{{ old('seo_description', $entity->seo_description) }}</textarea>
            <div class="form-text">Рекомендуется 120–160 символов</div>
        </div>

        {{-- Превью в Google --}}
        <div class="border rounded-3 p-3 mb-4 bg-light">
            <div class="text-muted small mb-2 fw-medium">Превью в Google</div>
            <div style="font-size:18px;color:#1a0dab;text-decoration:underline;">
                {{ $entity->seo_title ?: $entity->name . ' — Зверозор' }}
            </div>
            <div style="font-size:13px;color:#006621;">
                {{ url($type . 's/' . $entity->slug) }}
            </div>
            <div style="font-size:13px;color:#545454;margin-top:2px;">
                {{ $entity->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($entity->description ?? ''), 160) }}
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary px-5 rounded-pill">
                💾 Сохранить SEO
            </button>
        </div>
    </form>
</div>
@endif
