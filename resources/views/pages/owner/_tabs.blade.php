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
@if(\$activeTab === 'promotions')
@include('pages.owner._tab-promotions', compact('entity', 'entityId', 'type'))
@endif

@if(\$activeTab === 'services')
<div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
    <h5 class="fw-bold mb-4">💊 Услуги и цены</h5>

    {{-- Форма добавления --}}
    <div class="border rounded-3 p-4 mb-4 bg-light">
        <h6 class="fw-semibold mb-3">Добавить / обновить цену</h6>

        @if(isset($relevantServices) && $relevantServices->isEmpty())
            <div class="alert alert-warning rounded-3 mb-3" style="font-size:13px;">
                Для вашей специализации в каталоге пока нет готовых услуг — воспользуйтесь
                поиском, чтобы найти и добавить услугу из общего списка.
            </div>
        @endif

        <div class="row g-3 align-items-end">

            {{-- Select с живым поиском по услугам --}}
            <div class="col-md-6">
                <label class="form-label fw-medium small">Услуга</label>
                <div class="service-search-wrap position-relative">
                    <input type="text"
                           id="service-search-input"
                           class="form-control"
                           placeholder="Выберите из списка или начните вводить для поиска..."
                           autocomplete="off">
                    <input type="hidden" id="price-service" value="">
                    <div id="service-search-results" class="service-search-dropdown d-none"></div>
                </div>
                <div class="form-text" style="font-size:11px;">
                    По умолчанию показаны услуги вашей специализации. Чтобы найти любую другую — начните печатать.
                </div>
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

<style>
    .service-search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 50;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        max-height: 280px;
        overflow-y: auto;
        box-shadow: 0 8px 24px rgba(0,0,0,.12);
        margin-top: 4px;
    }
    .service-search-group-label {
        padding: 6px 14px 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #9ca3af;
        background: #fafafa;
        position: sticky;
        top: 0;
    }
    .service-search-item {
        padding: 9px 14px;
        cursor: pointer;
        font-size: 14px;
        border-bottom: 1px solid #f3f4f6;
    }
    .service-search-item:last-child { border-bottom: none; }
    .service-search-item:hover { background: #f0f5ff; }
    .service-search-item .svc-category {
        font-size: 11px;
        color: #9ca3af;
        margin-left: 6px;
    }
    .service-search-empty {
        padding: 10px 14px;
        color: #9ca3af;
        font-size: 13px;
    }
</style>

<script>
(function () {
    // Услуги, релевантные специализации текущего специалиста/организации —
    // показываются по умолчанию, до того как пользователь начал что-то искать
    @php
        $relevantForJs = ($relevantServices ?? collect())->map(function ($s) {
            return ['id' => $s->id, 'name' => $s->name];
        })->values();
    @endphp
    const relevantServices = {!! json_encode($relevantForJs, JSON_UNESCAPED_UNICODE) !!};

    // Полный список услуг — подключается только когда пользователь печатает в поиске,
    // чтобы можно было добавить любую услугу, не только свою специализацию
    @php
        $allForJs = ($allServices ?? collect())->map(function ($s) {
            return [
                'id'       => $s->id,
                'name'     => $s->name,
                'category' => $s->specialization_doctor ?: ($s->specialization ?: ''),
            ];
        })->values();
    @endphp
    const allServices = {!! json_encode($allForJs, JSON_UNESCAPED_UNICODE) !!};

    const input        = document.getElementById('service-search-input');
    const hiddenInput   = document.getElementById('price-service');
    const dropdown      = document.getElementById('service-search-results');

    function renderRelevant() {
        if (!relevantServices.length) {
            dropdown.innerHTML = '<div class="service-search-empty">Нет готовых услуг для вашей специализации — начните вводить текст, чтобы найти услугу в общем списке</div>';
            dropdown.classList.remove('d-none');
            return;
        }
        dropdown.innerHTML =
            '<div class="service-search-group-label">Услуги вашей специализации</div>' +
            relevantServices.map(svc => `
                <div class="service-search-item" data-id="${svc.id}" data-name="${svc.name.replace(/"/g, '&quot;')}">
                    ${svc.name}
                </div>
            `).join('');
        dropdown.classList.remove('d-none');
    }

    function renderSearch(list) {
        if (!list.length) {
            dropdown.innerHTML = '<div class="service-search-empty">Ничего не найдено</div>';
            dropdown.classList.remove('d-none');
            return;
        }
        dropdown.innerHTML =
            '<div class="service-search-group-label">Результаты поиска (все услуги)</div>' +
            list.slice(0, 50).map(svc => `
                <div class="service-search-item" data-id="${svc.id}" data-name="${svc.name.replace(/"/g, '&quot;')}">
                    ${svc.name}
                    ${svc.category ? `<span class="svc-category">${svc.category}</span>` : ''}
                </div>
            `).join('');
        dropdown.classList.remove('d-none');
    }

    input.addEventListener('focus', function () {
        // По умолчанию показываем услуги своей специализации
        if (!this.value.trim()) {
            renderRelevant();
        }
    });

    input.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        hiddenInput.value = ''; // сброс выбора при ручном вводе

        if (!q) {
            renderRelevant();
            return;
        }
        // Поиск идёт по ПОЛНОМУ списку услуг — можно найти и добавить любую
        const filtered = allServices.filter(s => s.name.toLowerCase().includes(q));
        renderSearch(filtered);
    });

    dropdown.addEventListener('click', function (e) {
        const item = e.target.closest('.service-search-item');
        if (!item) return;
        hiddenInput.value = item.dataset.id;
        input.value = item.dataset.name;
        dropdown.classList.add('d-none');
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.service-search-wrap')) {
            dropdown.classList.add('d-none');
        }
    });

    // ── Сохранение цены ──
    document.getElementById('price-save-btn')?.addEventListener('click', function () {
        const serviceId = hiddenInput.value;
        const price     = document.getElementById('price-amount').value;
        const currency  = document.getElementById('price-currency').value;
        const status    = document.getElementById('price-save-status');

        if (!serviceId || !price) { status.textContent = 'Выберите услугу из списка и укажите цену'; return; }

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
            } else {
                status.textContent = data.message || 'Ошибка сохранения';
            }
        })
        .catch(() => { status.textContent = 'Ошибка'; });
    });

    // ── Удаление цены (делегирование — работает и для строк, добавленных позже) ──
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete-price');
        if (!btn) return;

        if (!confirm('Удалить эту цену?')) return;

        const priceId = btn.dataset.id;
        const row = document.getElementById('price-row-' + priceId);

        btn.disabled = true;

        fetch('/owner/prices/' + priceId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                row?.remove();

                // Если строк не осталось — показываем плейсхолдер "Цены не добавлены"
                const tbody = document.querySelector('#prices-table tbody');
                if (tbody && !tbody.querySelector('tr')) {
                    tbody.innerHTML = '<tr id="no-prices-row"><td colspan="3" class="text-center text-muted py-3">Цены не добавлены</td></tr>';
                }
            } else {
                btn.disabled = false;
                alert(data.message || 'Не удалось удалить цену');
            }
        })
        .catch(() => {
            btn.disabled = false;
            alert('Ошибка при удалении');
        });
    });
})();
</script>
@endif

