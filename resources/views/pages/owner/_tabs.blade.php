{{--
    @include('pages.owner._tabs', ['entity' => $clinic, 'type' => 'clinic', 'entityId' => $clinic->id])
--}}

@include('pages.owner._entity_selector', ['entityId' => $entityId, 'type' => $type])


@php $activeTab = request('tab', 'info'); @endphp

{{-- ════════════════ ВКЛАДКА: ОСНОВНАЯ ИНФОРМАЦИЯ ════════════════ --}}
@if($activeTab === 'info')
    @if(in_array($type, ['organization', 'clinic']))
        @include('pages.owner._form-organization', ['entity' => $entity, 'type' => $type, 'entityId' => $entityId])
    @else
        @include('pages.owner._form-specialist', ['entity' => $entity, 'type' => $type, 'entityId' => $entityId])
    @endif
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
                    <option value="₽">₽</option>
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

