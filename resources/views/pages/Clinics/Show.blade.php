@extends('layouts.app')

@section('title', $clinic->name)

@section('content')
{{-- Оборачиваем всю страницу в flex-контейнер, чтобы футер прижимался вниз --}}
<div class="d-flex flex-column min-vh-100 bg-white">

    {{-- Хедер --}}
    @include('layouts.header')

    {{-- Контент --}}
    <main class="flex-grow-1 container py-5">
        <div class="max-w-3xl mx-auto">

            @php
                // Проверяем наличие логотипа
                $logo = !empty($clinic->logo)
                    ? asset('storage/' . $clinic->logo)
                    : asset('storage/clinics/logo/default.webp');
            @endphp

            {{-- Логотип + название в одной строке --}}
            <div class="d-flex align-items-center justify-content-center mb-4 flex-wrap text-center text-md-start">
                <img src="{{ $logo }}" 
                     alt="{{ $clinic->name }}" 
                     class="logo_clinic_card me-3 mb-3 mb-md-0 object-contain">
                <h1 class="text-2xl fw-bold m-0">{{ $clinic->name }}</h1>
            </div>

            <div class="text-secondary mb-4 text-center text-md-start">
                <div>📍 {{ $clinic->country }}, {{ $clinic->region }}, {{ $clinic->city }}, {{ $clinic->street }} {{ $clinic->house }}</div>
                <div>🕒 {{ $clinic->workdays }} — {{ $clinic->schedule }}</div>
                <div>📞 {{ $clinic->phone1 }} @if($clinic->phone2), {{ $clinic->phone2 }}@endif</div>
                <div>✉️ {{ $clinic->email }}</div>
                @if($clinic->telegram)
                    <div>💬 Telegram: {{ $clinic->telegram }}</div>
                @endif
                @if($clinic->whatsapp)
                    <div>💬 WhatsApp: {{ $clinic->whatsapp }}</div>
                @endif
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
    </main>

    {{-- Футер (на всю ширину, прижат к низу) --}}
    <footer class="footer-fullwidth mt-auto w-100">
        @include('layouts.footer')
    </footer>
</div>

<style>
.logo_clinic_card {
    width: 96px;
    height: 96px;
    object-fit: contain;
    border-radius: 8px;
    background-color: #f8f9fa;
    padding: 6px;
    border: 1px solid #eee;
}

.footer-fullwidth {
    width: 100%;
    margin-top: auto;
    background-color: #f8f9fa;
    border-top: 1px solid #e5e5e5;
}

/* Убедимся, что футер действительно прижат к низу */
html, body {
    height: 100%;
    margin: 0;
}

@media (max-width: 576px) {
    .logo_clinic_card {
        width: 80px;
        height: 80px;
    }
    h1.text-2xl {
        font-size: 1.5rem;
    }
}
.py-4 {
    padding-top: 0 !important;
}
</style>
@endsection
