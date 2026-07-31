{{--
    Тело карточки заявки на верификацию: кнопки действий, статус отказа,
    список документов, форма загрузки, чат с администратором.

    Ожидает: $ownerRow, $entityType, $entityName, $icon
--}}
@php
    $isRejected  = (bool) ($ownerRow->is_rejected ?? false);
    $rejectedAt  = $ownerRow->rejected_at ?? null;
    $canReapply  = $isRejected && $ownerRow->canReapply();
    $daysLeft    = 0;
    if ($isRejected && $rejectedAt && !$canReapply) {
        $daysLeft = 7 - (int) \Carbon\Carbon::now()->diffInDays($rejectedAt);
    }
    $entityId  = $ownerRow->{$entityType . '_id'};
    $documents = $ownerRow->documents ?? collect();
@endphp

<div class="d-flex justify-content-end gap-2 flex-wrap mb-4">
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
