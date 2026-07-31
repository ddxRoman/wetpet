@extends('layouts.app')

<title>Заявка на рассмотрении — {{ $entityName }} — Зверозор</title>

@section('content')
@include('layouts.header')

<div class="container my-4" style="max-width: 680px;">

    {{-- Заголовок --}}
    <div class="text-center mb-5">
        <div style="font-size:56px;" class="mb-3">⏳</div>
        <h2 class="fw-bold text-dark mb-2">Заявка на рассмотрении</h2>
        <p class="text-muted">
            Администратор проверит данные и подтвердит доступ в течение 1–2 рабочих дней.
            Чтобы ускорить проверку — загрузите подтверждающие документы.
        </p>
    </div>

    {{-- Переключатель между объектами пользователя (если их больше одного) --}}
    @include('pages.owner._entity_selector', [
        'allUserEntities' => $allUserEntities ?? collect(),
        'entityId'        => $entityId,
        'type'            => $type,
    ])

    <div class="card border-0 shadow-sm rounded-4 mb-4 mt-4">
        <div class="card-body p-4">

            <div class="d-flex align-items-center gap-3 mb-4">
                <span style="font-size:32px;">{{ $icon }}</span>
                <div>
                    <h5 class="fw-bold mb-1">{{ $entityName }}</h5>
                    @if($ownerRow->is_rejected ?? false)
                        <span class="badge bg-danger" style="font-size:11px;">❌ Отказано</span>
                    @else
                        <span class="badge bg-warning text-dark" style="font-size:11px;">⏳ На проверке</span>
                    @endif
                </div>
            </div>

            @include('pages.owner._pending_entity_body', [
                'ownerRow'   => $ownerRow,
                'entityType' => $type,
                'entityName' => $entityName,
                'icon'       => $icon,
            ])

        </div>
    </div>

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
