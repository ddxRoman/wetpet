@extends('layouts.app')

<title>Заявка на рассмотрении — Зверозор</title>

@section('content')
@include('layouts.header')

<style>
    .claim-accordion-item {
        overflow: hidden;
    }

    .claim-accordion-header {
        cursor: pointer;
        user-select: none;
        transition: background-color 0.15s ease;
    }
    .claim-accordion-header:hover {
        background-color: #f8f9fa;
    }
    .claim-accordion-header:focus-visible {
        outline: 2px solid #2ecc71;
        outline-offset: -2px;
    }

    .claim-accordion-number {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 8px;
        border-radius: 50px;
        background-color: #f4f9f4;
        border: 1px solid #dcefdc;
        color: #3d8b4c;
        font-weight: 700;
        font-size: 13px;
    }

    .claim-accordion-toggle-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 1px solid #dee2e6;
        background-color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #495057;
        transition: transform 0.2s ease, background-color 0.2s ease;
    }
    .claim-accordion-toggle-btn i {
        transition: transform 0.2s ease;
    }
    .claim-accordion-header:hover .claim-accordion-toggle-btn {
        background-color: #f1f3f5;
    }
    .claim-accordion-header[aria-expanded="true"] .claim-accordion-toggle-btn i {
        transform: rotate(180deg);
    }
</style>

<div class="container my-5" style="max-width: 680px;">

   {{-- Заголовок --}}
    <div class="text-center mb-5">
        <div style="font-size:56px;" class="mb-3">⏳</div>
        <h2 class="fw-bold text-dark mb-2">Заявка на рассмотрении</h2>
        <p class="text-muted">
            Администратор проверит данные и подтвердит доступ в течение 1–2 рабочих дней.
            Чтобы ускорить проверку — загрузите подтверждающие документы.
        </p>
    </div>

    {{-- ВСТАВЛЯЕМ СЮДА ПАНЕЛЬ ПЕРЕКЛЮЧЕНИЯ --}}
    @include('pages.owner._entity_selector')

    @if(isset($pendingOwners) && $pendingOwners->isNotEmpty())

        <div class="claim-accordion">
        @foreach($pendingOwners as $index => $item)
            @php
                $ownerRow   = $item['owner_row'];
                $entityType = $item['entity_type'];
                $entityName = $item['entity_name'];
                $icon       = $item['icon'];
                $documents  = $ownerRow->documents ?? collect();
            @endphp

            <div class="card claim-accordion-item border-0 shadow-sm rounded-4 mb-4">

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
                    $collapseId = 'claim-collapse-' . $ownerRow->id;
                @endphp

                {{-- Заголовок аккордиона: номер, статус, кнопка разворачивания --}}
                <div class="claim-accordion-header d-flex align-items-center justify-content-between gap-3 p-4"
                     role="button"
                     tabindex="0"
                     data-bs-toggle="collapse"
                     data-bs-target="#{{ $collapseId }}"
                     aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                     aria-controls="{{ $collapseId }}">

                    <div class="d-flex align-items-center gap-3 flex-grow-1 min-w-0">
                        <span class="claim-accordion-number">№{{ $index + 1 }}</span>
                        <span style="font-size:32px;">{{ $icon }}</span>
                        <div class="min-w-0">
                            <h5 class="fw-bold mb-1 text-truncate">{{ $entityName }}</h5>
                            @if($isRejected)
                                <span class="badge bg-danger" style="font-size:11px;">❌ Отказано</span>
                            @else
                                <span class="badge bg-warning text-dark" style="font-size:11px;">⏳ На проверке</span>
                            @endif
                        </div>
                    </div>

                    <button type="button" class="claim-accordion-toggle-btn flex-shrink-0" aria-label="Развернуть/свернуть заявку">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>

                <div id="{{ $collapseId }}" class="collapse {{ $index === 0 ? 'show' : '' }}">
                    <div class="card-body p-4 pt-0">
                        @include('pages.owner._pending_entity_body', [
                            'ownerRow'   => $ownerRow,
                            'entityType' => $entityType,
                            'entityName' => $entityName,
                            'icon'       => $icon,
                        ])
                    </div>
                </div>
            </div>
        @endforeach
        </div>

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

@include('pages.owner._claim_scripts')
@endsection
