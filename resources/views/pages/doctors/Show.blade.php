@extends('layouts.app')
@vite([
    'resources/css/main.css',
    'resources/sass/app.scss',
    'resources/js/app.js',
    'resources/js/account/account.js',
])

@section('content')
@php
    // Если нет фото — подставляем дефолт
    $photo = $doctor->photo ? asset('storage/'.$doctor->photo) : asset('storage/default-doctor.png');

    // Поля под карту
    $addressParts = array_filter([
        $doctor->city ?? '',
        $doctor->clinic ?? '',
    ]);

    $mapQuery = urlencode(implode(', ', $addressParts));
@endphp

<body>
    <div class="d-flex flex-column min-vh-100 bg-white">
    @include('layouts.header')

</body>

<div class="container mt-5">
    {{-- КНОПКА НАЗАД --}}
    <div class="mb-4">
        <a href="{{ route('doctors.index') }}"
           class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
            ← В каталог врачей
        </a>
    </div>

    {{-- ШАПКА --}}
    <div class="d-flex align-items-center flex-wrap mb-4">

        <img src="{{ $photo }}"
             style="width:90px;height:90px;border-radius:10px;border:1px solid #ddd;object-fit:cover"
             class="me-3">

        <div>
            <h1 class="fw-bold m-0">{{ $doctor->name }}</h1>

            <div class="text-muted">
                {{ $doctor->specialization }}
            </div>
        </div>
    </div>


    {{-- ТАБЫ --}}
    @php $tab = request('tab', 'contacts'); @endphp

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $tab==='contacts' ? 'active':'' }}"
               href="?tab=contacts">Контакты</a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ $tab==='services' ? 'active':'' }}"
               href="?tab=services">Услуги</a>
        </li>

        <li class="nav-item">
            <a class="nav-link {{ $tab==='reviews' ? 'active':'' }}"
               href="?tab=reviews">Отзывы</a>
        </li>
    </ul>


    <div class="row">
        {{-- ЛЕВАЯ КОЛОНКА --}}
        <div class="col-lg-8">

            {{-- 🔹 КОНТАКТЫ --}}
            @if($tab === 'contacts')
                <h4 class="fw-semibold mb-3">Контакты</h4>

                <ul class="list-unstyled text-secondary">

                    @if($doctor->clinic)
                    <a href="{{ route('clinics.show', $clinic->id) }}" class="text-decoration-none text-reset">
                        <li>🏥 {{ $doctor->clinic->name }}</li>
                    </a>    
                    @endif

                    @if($doctor->city)
                        <li>📍 {{ $doctor->city->name }}</li>
                    @endif

                    @if($doctor->experience)
                        <li>👨‍⚕️ Стаж: {{ $doctor->experience }} лет</li>
                    @endif

                    @if($doctor->phone)
                        <li>📞 <a href="tel:{{ $doctor->phone }}">{{ $doctor->phone }}</a></li>
                    @endif

                    @if($doctor->email)
                        <li>✉️ {{ $doctor->email }}</li>
                    @endif
                </ul>
            @endif


            

            {{-- 🔹 УСЛУГИ --}}
@if($tab === 'services')
    @include('pages.doctors.tabs.services', ['doctor' => $doctor])
@endif



            {{-- 🔹 ОТЗЫВЫ --}}
            @if($tab === 'reviews')
                <h4 class="fw-semibold mb-3">Отзывы</h4>

                @include('pages.doctors.tabs.reviews', ['clinic_id' => $doctor->clinic_id])


            @endif

        </div>


        {{-- ПРАВАЯ КОЛОНКА --}}
        <div class="col-lg-4">

            {{-- КАРТА --}}
            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <h6 class="fw-semibold">Карта / местоположение</h6>

                    <div class="rounded mt-2"
                         style="overflow:hidden;width:100%;height:260px;">

                        <iframe
                            width="100%"
                            height="100%"
                            frameborder="0"
                            src="https://www.orsdiplom.h1n.ru/action/autorization.php"
                            allowfullscreen>
                        </iframe>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
</div>
@endsection
