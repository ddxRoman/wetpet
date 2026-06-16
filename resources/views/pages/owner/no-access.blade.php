@extends('layouts.app')

<title>Ожидание подтверждения — Зверозор</title>

@section('content')
@include('layouts.header')

<div class="container my-5" style="max-width: 600px;">
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
        <div style="font-size:64px;" class="mb-3">⏳</div>
        <h2 class="fw-bold text-dark mb-2">Заявка на рассмотрении</h2>
        <p class="text-muted mb-4">
            Ваша заявка на управление страницей получена. Администратор проверит данные
            и подтвердит доступ в течение 1–2 рабочих дней.
        </p>

        <div class="alert alert-info rounded-3 text-start mb-4">
            <strong>Что проверяется:</strong>
            <ul class="mb-0 mt-2">
                <li>Документы о праве собственности или трудоустройства</li>
                <li>Соответствие указанных данных публичной информации</li>
            </ul>
        </div>

        <p class="text-muted small mb-4">
            По вопросам подтверждения пишите на
            <a href="mailto:{{ config('company.email', 'info@zverozor.ru') }}" class="text-primary fw-medium">
                {{ config('company.email', 'info@zverozor.ru') }}
            </a>
        </p>

        <a href="{{ route('account') }}" class="btn btn-outline-primary rounded-pill px-4">
            ← Вернуться в профиль
        </a>
    </div>
</div>

@include('layouts.footer')
@endsection
