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
    $photo = $doctor->photo ? asset('storage/'.$doctor->photo) : asset('storage/doctors/default-doctor.png');

    // Поля под карту
    $addressParts = array_filter([
        $doctor->city ?? '',
        $doctor->clinic ?? '',
    ]);

    $mapQuery = urlencode(implode(', ', $addressParts));
@endphp

<head>


</head>

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
<h1 class="fw-bold m-0 d-flex align-items-center gap-2">
    {{ $doctor->name }}

    @if($doctor->exotic_animals == 'Да' || $doctor->exotic_animals == 1 || $doctor->exotic_animals === true)
        <span title="Экзотические животные" class="badge bg-warning text-dark" style="font-size: 0.8rem;">
            🦎
        </span>
    @endif
</h1>


            <div class="text-muted">
                {{ $doctor->specialization }}
            </div>
        </div>
    </div>


    {{-- ТАБЫ --}}
    @php $tab = request('tab', 'info'); @endphp

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $tab==='info' ? 'active':'' }}"
               href="?tab=info">Информация</a>
        </li>

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
            
            {{-- 🔹 УСЛУГИ --}}
        @if($tab === 'info')
        @include('pages.doctors.tabs.info', ['doctor' => $doctor])
        @endif
            
            {{-- 🔹 КОНТАКТЫ --}}
            @if($tab === 'contacts')
                   @include('pages.doctors.tabs.contacts', ['doctor' => $doctor])
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



        <style>
            .fw-semibold1{
                font-weight: 300;
            }
            .doctor_fw-semibold{
            color: #444444be;
            }
        </style>



        {{-- ПРАВАЯ КОЛОНКА --}}
        <div class="col-lg-4">

            {{-- КАРТА --}}
            <div class="card shadow-sm border-0">
                <div class="card-body">

                   <span class="doctor_fw-semibold"> Врач-{{ $doctor->specialization }}: </span>
                    <span class="fw-semibold">
                   {{ $doctor->name }}</span>

                    <div class="rounded mt-2"
                         style="overflow:hidden;width:100%;height:260px;">

        <img src="{{ $photo }}"
             style="width:290px;height:290px;border-radius:10px;border:1px solid #ddd;object-fit:cover"
             class="me-3">

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
</div>
@endsection
