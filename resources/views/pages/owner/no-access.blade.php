@extends('layouts.app')

<title>Заявка на рассмотрении — Зверозор</title>

@section('content')
@include('layouts.header')





<div class="container my-5" style="max-width: 680px;">

   {{-- Заголовок --}}
    <div class="text-center mb-5">
        <div style="font-size:56px;" class=\"mb-3\">⏳</div>
        <h2 class="fw-bold text-dark mb-2">Заявка на рассмотрении</h2>
        <p class="text-muted">
            Администратор проверит данные и подтвердит доступ в течение 1–2 рабочих дней.
            Чтобы ускорить проверку — загрузите подтверждающие документы.
        </p>
    </div>

    {{-- ВСТАВЛЯЕМ СЮДА ПАНЕЛЬ ПЕРЕКЛЮЧЕНИЯ --}}
    @include('pages.owner._entity_selector')

    @if(isset($pendingOwners) && $pendingOwners->isNotEmpty())

    @if(isset($pendingOwners) && $pendingOwners->isNotEmpty())

        @foreach($pendingOwners as $item)
            @php
                $ownerRow   = $item['owner_row'];
                $entityType = $item['entity_type'];
                $entityName = $item['entity_name'];
                $icon       = $item['icon'];
                $documents  = $ownerRow->documents ?? collect();
            @endphp

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">

                    {{-- Шапка карточки --}}
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span style="font-size:32px;">{{ $icon }}</span>
                        <div>
                            <h5 class="fw-bold mb-0">{{ $entityName }}</h5>
                            <span class="badge bg-warning text-dark" style="font-size:11px;">На проверке</span>
                        </div>
                    </div>

                    {{-- Комментарий администратора (если отклонили) --}}
                    @if($ownerRow->admin_comment)
                        <div class="alert alert-danger rounded-3 mb-4">
                            <strong>💬 Комментарий администратора:</strong>
                            <div class="mt-1">{{ $ownerRow->admin_comment }}</div>
                        </div>
                    @endif

                    {{-- Список уже загруженных документов --}}
                    @if($documents->isNotEmpty())
                        <div class="mb-4">
                            <div class="fw-semibold mb-2" style="font-size:14px;">Загруженные документы:</div>
                            <div class="d-flex flex-column gap-2" id="docs-list-{{ $ownerRow->id }}">
                                @foreach($documents as $doc)
                                    <div class="d-flex align-items-center justify-content-between
                                                border rounded-3 px-3 py-2 bg-light"
                                         id="doc-row-{{ $doc->id }}">
                                        <a href="{{ Storage::url($doc->path) }}"
                                           target="_blank"
                                           class="text-decoration-none text-dark d-flex align-items-center gap-2">
                                            <span>📄</span>
                                            <span>{{ $doc->original_name ?: 'Документ #' . $doc->id }}</span>
                                        </a>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($doc->comment)
                                                <span class="text-muted small">{{ $doc->comment }}</span>
                                            @endif
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger rounded-pill btn-delete-doc"
                                                    data-id="{{ $doc->id }}"
                                                    style="padding: 2px 10px; font-size:12px;">
                                                Удалить
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Форма загрузки документа --}}
                    <div class="border rounded-3 p-3 bg-light">
                        <div class="fw-semibold mb-1" style="font-size:14px;">Добавить документ</div>
                        <div class="text-muted mb-3" style="font-size:12px;">
                            Подойдёт: выписка ЕГРЮЛ/ЕГРИП, свидетельство о регистрации,
                            доверенность, диплом, трудовой договор. Формат: PDF, JPG, PNG.
                        </div>

                        <form class="doc-upload-form"
                              data-owner-row-id="{{ $ownerRow->id }}"
                              data-entity-type="{{ $entityType }}"
                              data-docs-list="docs-list-{{ $ownerRow->id }}">
                            @csrf
                            <div class="row g-2 align-items-end">
                                <div class="col-md-7">
                                    <input type="file"
                                           name="document"
                                           class="form-control"
                                           accept=".pdf,.jpg,.jpeg,.png,.webp"
                                           required>
                                </div>
                                <div class="col-md-5">
                                    <input type="text"
                                           name="comment"
                                           class="form-control"
                                           placeholder="Комментарий (необязательно)">
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3 mt-3">
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    ⬆️ Загрузить
                                </button>
                                <span class="upload-status text-muted small"></span>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        @endforeach

    @else
        {{-- Нет pending-заявок — значит просто ждём (заявка только что создана) --}}
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="alert alert-info rounded-3 mb-0">
                <strong>Что проверяется:</strong>
                <ul class="mb-0 mt-2">
                    <li>Документы о праве собственности или трудоустройства</li>
                    <li>Соответствие указанных данных публичной информации</li>
                </ul>
            </div>
        </div>
    @endif

    <div class="text-center mt-4">
        <p class="text-muted small mb-2">
            По вопросам подтверждения пишите на
            <a href="mailto:{{ config('company.email', 'info@zverozor.ru') }}" class="text-primary">
                {{ config('company.email', 'info@zverozor.ru') }}
            </a>
        </p>
        <a href="{{ route('account') }}" class="btn btn-outline-secondary rounded-pill px-4">
            ← Вернуться в профиль
        </a>
    </div>
</div>

@include('layouts.footer')

<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    // ── Загрузка документа ────────────────────────────────────
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form.classList.contains('doc-upload-form')) return;
        e.preventDefault();

        const status     = form.querySelector('.upload-status');
        const ownerRowId = form.dataset.ownerRowId;
        const entityType = form.dataset.entityType;
        const docsList   = document.getElementById(form.dataset.docsList);

        const fd = new FormData(form);
        fd.set('owner_row_id', ownerRowId);
        fd.set('entity_type', entityType);

        status.textContent = 'Загрузка…';

        fetch('{{ route("owner.documents.upload") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: fd,
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                status.textContent = '⚠️ Ошибка загрузки';
                return;
            }

            status.textContent = '✓ Загружено';

            // Добавляем строку документа в список без перезагрузки
            if (docsList) {
                const row = document.createElement('div');
                row.className = 'd-flex align-items-center justify-content-between border rounded-3 px-3 py-2 bg-light';
                row.id = 'doc-row-' + data.document.id;
                row.innerHTML = `
                    <a href="${data.document.url}" target="_blank"
                       class="text-decoration-none text-dark d-flex align-items-center gap-2">
                        <span>📄</span><span>${data.document.name}</span>
                    </a>
                    <button type="button"
                            class="btn btn-sm btn-outline-danger rounded-pill btn-delete-doc"
                            data-id="${data.document.id}"
                            style="padding:2px 10px;font-size:12px;">
                        Удалить
                    </button>`;
                docsList.appendChild(row);
            }

            // Сбрасываем форму
            form.reset();
            setTimeout(() => { status.textContent = ''; }, 3000);
        })
        .catch(() => { status.textContent = '⚠️ Ошибка загрузки'; });
    });

    // ── Удаление документа ────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete-doc');
        if (!btn) return;
        if (!confirm('Удалить документ?')) return;

        fetch('/owner/documents/' + btn.dataset.id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('doc-row-' + btn.dataset.id)?.remove();
            }
        });
    });
})();
</script>
@endsection
@endif