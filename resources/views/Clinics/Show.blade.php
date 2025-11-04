@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="max-w-3xl mx-auto">
        <img src="{{ $clinic->logo ?? '/images/no-logo.png' }}" alt="{{ $clinic->name }}" class="w-32 h-32 mx-auto mb-4 object-contain">
        <h1 class="text-2xl fw-bold text-center mb-4">{{ $clinic->name }}</h1>

        <div class="text-secondary mb-4">
            <div>📍 {{ $clinic->country }}, {{ $clinic->region }}, {{ $clinic->city }}, {{ $clinic->street }} {{ $clinic->house }}</div>
            <div>🕒 {{ $clinic->workdays }} — {{ $clinic->schedule }}</div>
            <div>📞 {{ $clinic->phone1 }} @if($clinic->phone2), {{ $clinic->phone2 }}@endif</div>
            <div>✉️ {{ $clinic->email }}</div>
            @if($clinic->telegram)<div>💬 Telegram: {{ $clinic->telegram }}</div>@endif
            @if($clinic->whatsapp)<div>💬 WhatsApp: {{ $clinic->whatsapp }}</div>@endif
        </div>

        <div class="mb-4">
            <h2 class="fs-5 fw-semibold mb-2">Описание</h2>
            <p class="text-body">{{ $clinic->description }}</p>
        </div>

        <div class="mb-4">
            <h2 class="fs-5 fw-semibold mb-2">Услуги</h2>
            <ul>
                @foreach($clinic->services ?? [] as $service)
                    <li>Услуга #{{ $service }}</li>
                @endforeach
            </ul>
        </div>

        <div class="mb-4">
            <h2 class="fs-5 fw-semibold mb-2">Доктора</h2>
            <ul>
                @foreach($clinic->doctors ?? [] as $doctor)
                    <li>Доктор #{{ $doctor }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
