@extends('layouts.app')

@php
    $typeLabels = [
        'clinic'       => ['icon' => '🏥',   'name' => 'Клиника'],
        'organization' => ['icon' => '🏢',   'name' => 'Организация'],
        'doctor'       => ['icon' => '👨‍⚕️', 'name' => 'Профиль врача'],
        'specialist'   => ['icon' => '🩺',   'name' => 'Профиль специалиста'],
    ];
    $label = $typeLabels[$type] ?? ['icon' => '❓', 'name' => 'Объект'];
    $hasOthers = isset($allUserEntities) && $allUserEntities->isNotEmpty();
@endphp

<title>Объект не найден — Зверозор</title>

@section('content')
@include('layouts.header')

<div class="container my-5" style="max-width: 640px;">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5 text-center">
            <div style="font-size:56px;" class="mb-3">{{ $label['icon'] }}</div>
            <h2 class="fw-bold text-dark mb-2">{{ $label['name'] }} не найдена</h2>
            <p class="text-muted mb-4">
                Эта запись была удалена из базы данных, поэтому управлять ей больше нельзя.
                Мы убрали ссылку на неё из вашего профиля.
                Если это ошибка — добавьте организацию или специалиста заново.
            </p>

            <div class="d-flex flex-wrap justify-content-center gap-2">
                @if($hasOthers)
                    <a href="{{ route('owner.index') }}" class="btn btn-primary rounded-pill px-4">
                        Перейти в другой кабинет
                    </a>
                @endif
                <a href="{{ route('account') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    В личный кабинет
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
