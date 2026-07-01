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
                    @php
                        $isRejected  = (bool) ($ownerRow->is_rejected ?? false);
                        $rejectedAt  = $ownerRow->rejected_at ?? null;
                        $canReapply  = $isRejected && $ownerRow->canReapply();
                        $daysLeft    = 0;
                        if ($isRejected && $rejectedAt && !$canReapply) {
                            $daysLeft = 7 - (int) \Carbon\Carbon::now()->diffInDays($rejectedAt);
                        }
                        $entityId = $ownerRow->{$entityType . '_id'};
                    @endphp

                    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
                        <div class="d-flex align-items-center gap-3">
                            <span style="font-size:32px;">{{ $icon }}</span>
                            <div>
                                <h5 class="fw-bold mb-0">{{ $entityName }}</h5>
                                @if($isRejected)
                                    <span class="badge bg-danger" style="font-size:11px;">❌ Отказано</span>
                                @else
                                    <span class="badge bg-warning text-dark" style="font-size:11px;">⏳ На проверке</span>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            @if($isRejected && $canReapply)
                                {{-- Можно подать повторно --}}
                                <a href="{{ url("/{$entityType}s/{$ownerRow->{$entityType}?->slug}") }}"
                                   class="btn btn-sm btn-success rounded-pill"
                                   style="font-size:13px;">
                                    🔄 Подать повторно
                                </a>
                            @endif
                            @if(!$isRejected || $canReapply)
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger rounded-pill btn-cancel-claim"
                                        data-type="{{ $entityType }}"
                                        data-id="{{ $entityId }}"
                                        data-name="{{ $entityName }}"
                                        style="font-size:13px;">
                                    ✕ Отменить заявку
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Статус отказа с таймером --}}
                    @if($isRejected)
                        <div class="alert rounded-3 mb-4 {{ $canReapply ? 'alert-warning' : 'alert-danger' }}">
                            <div class="fw-bold mb-1">❌ Ваша заявка отклонена</div>
                            @if($ownerRow->admin_comment)
                                <div class="mb-2">
                                    <strong>Причина:</strong> {{ $ownerRow->admin_comment }}
                                </div>
                            @endif
                            @if($canReapply)
                                <div class="text-success fw-semibold">
                                    ✅ Прошло 7 дней — вы можете подать повторную заявку.
                                </div>
                            @else
                                <div>
                                    Повторная заявка будет доступна через
                                    <strong>{{ $daysLeft }} {{ $daysLeft === 1 ? 'день' : ($daysLeft < 5 ? 'дня' : 'дней') }}</strong>.
                                </div>
                            @endif
                        </div>
                    @elseif($ownerRow->admin_comment)
                        <div class="alert alert-info rounded-3 mb-4">
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
                                           name="documents[]"
                                           class="form-control"
                                           accept=".pdf,.jpg,.jpeg,.png,.webp"
                                           multiple
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

                    {{-- ═══════════ ЧАТ С АДМИНИСТРАТОРОМ ═══════════ --}}
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="fw-semibold mb-3 d-flex align-items-center gap-2">
                            💬 Сообщения от администратора
                        </h6>

                        <div class="claim-chat-box border rounded-3 p-3 mb-3"
                             id="chat-box-{{ $ownerRow->id }}"
                             data-owner-row-id="{{ $ownerRow->id }}"
                             data-entity-type="{{ $entityType }}"
                             style="max-height:280px;overflow-y:auto;background:#f8f9fa;">
                            <div class="text-muted text-center py-3 small chat-loading">Загрузка сообщений…</div>
                        </div>

                        <form class="claim-chat-form d-flex gap-2"
                              data-owner-row-id="{{ $ownerRow->id }}"
                              data-entity-type="{{ $entityType }}"
                              data-chat-box="chat-box-{{ $ownerRow->id }}">
                            @csrf
                            <input type="text"
                                   name="message"
                                   class="form-control"
                                   placeholder="Напишите сообщение администратору..."
                                   maxlength="2000"
                                   required>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 flex-shrink-0">
                                Отправить
                            </button>
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

            // Добавляем строки документов в список без перезагрузки
            // (контроллер возвращает МАССИВ documents, т.к. поддерживается мультизагрузка)
            if (docsList && Array.isArray(data.documents)) {
                data.documents.forEach(doc => {
                    const row = document.createElement('div');
                    row.className = 'd-flex align-items-center justify-content-between border rounded-3 px-3 py-2 bg-light';
                    row.id = 'doc-row-' + doc.id;
                    row.innerHTML = `
                        <a href="${doc.url}" target="_blank"
                           class="text-decoration-none text-dark d-flex align-items-center gap-2">
                            <span>📄</span><span>${doc.name}</span>
                        </a>
                        <button type="button"
                                class="btn btn-sm btn-outline-danger rounded-pill btn-delete-doc"
                                data-id="${doc.id}"
                                style="padding:2px 10px;font-size:12px;">
                            Удалить
                        </button>`;
                    docsList.appendChild(row);
                });
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

    // ── Отмена заявки ─────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-cancel-claim');
        if (!btn) return;

        if (!confirm(`Отменить заявку на «${btn.dataset.name}»? Все загруженные документы будут удалены.`)) return;

        btn.disabled = true;
        btn.textContent = 'Отмена…';

        fetch(`/owner/claim/${btn.dataset.type}/${btn.dataset.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Убираем карточку заявки из DOM
                btn.closest('.card')?.remove();

                // Если карточек больше нет — перезагружаем страницу
                // чтобы показать пустое состояние или редирект
                const remaining = document.querySelectorAll('.btn-cancel-claim');
                if (remaining.length === 0) {
                    window.location.reload();
                }
            } else {
                btn.disabled = false;
                btn.textContent = '✕ Отменить заявку';
                alert(data.message || 'Не удалось отменить заявку.');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.textContent = '✕ Отменить заявку';
            alert('Ошибка соединения. Попробуйте ещё раз.');
        });
    });

    // ── ЧАТ С АДМИНИСТРАТОРОМ ───────────────────────────────────
    function renderChatMessages(box, messages) {
        if (!messages.length) {
            box.innerHTML = '<div class="text-muted text-center py-3 small">Сообщений пока нет. Напишите администратору, если у вас есть вопросы.</div>';
            return;
        }
        box.innerHTML = messages.map(m => `
            <div class="d-flex ${m.is_admin ? 'justify-content-start' : 'justify-content-end'} mb-2">
                <div class="rounded-3 px-3 py-2" style="max-width:80%;background:${m.is_admin ? '#fff' : '#d1f0ff'};border:1px solid ${m.is_admin ? '#e0e0e0' : '#b3e0ff'};">
                    <div class="fw-semibold small" style="font-size:11px;color:${m.is_admin ? '#dc3545' : '#0d6efd'};">
                        ${m.is_admin ? '👤 Администратор' : '🧑 Вы'}
                    </div>
                    <div style="font-size:13px;white-space:pre-wrap;">${m.text.replace(/</g, '&lt;')}</div>
                    <div class="text-muted text-end" style="font-size:10px;">${m.created_at}</div>
                </div>
            </div>
        `).join('');
        box.scrollTop = box.scrollHeight;
    }

    function loadChatMessages(box) {
        const ownerRowId = box.dataset.ownerRowId;
        const entityType = box.dataset.entityType;

        fetch(`{{ route('owner.claim.messages.get') }}?owner_row_id=${ownerRowId}&entity_type=${entityType}`, {
            headers: { 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) renderChatMessages(box, data.messages);
        })
        .catch(() => {});
    }

    // Загружаем сообщения для всех чатов при загрузке страницы
    document.querySelectorAll('.claim-chat-box').forEach(box => {
        loadChatMessages(box);
        // Обновляем каждые 10 секунд (простой поллинг вместо вебсокетов)
        setInterval(() => loadChatMessages(box), 10000);
    });

    // Отправка сообщения
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form.classList.contains('claim-chat-form')) return;
        e.preventDefault();

        const input      = form.querySelector('input[name="message"]');
        const message     = input.value.trim();
        if (!message) return;

        const ownerRowId = form.dataset.ownerRowId;
        const entityType = form.dataset.entityType;
        const box        = document.getElementById(form.dataset.chatBox);
        const btn        = form.querySelector('button[type="submit"]');

        btn.disabled = true;

        fetch('{{ route("owner.claim.messages.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept':       'application/json',
            },
            body: JSON.stringify({
                owner_row_id: ownerRowId,
                entity_type:  entityType,
                message:      message,
            }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                loadChatMessages(box);
            }
        })
        .finally(() => { btn.disabled = false; });
    });
})();
</script>
@endsection
