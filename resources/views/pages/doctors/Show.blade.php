@extends('layouts.app')

@section('content')
@php
    if (!isset($doctor)) {
        abort(404);
    }

    $photo = $doctor->photo && file_exists(public_path('storage/'.$doctor->photo))
        ? asset('storage/'.$doctor->photo)
        : asset('storage/doctors/default-doctor.png');

    $addressParts = array_filter([
        $doctor->city ?? '',
        $doctor->clinic ?? '',
    ]);

    $mapQuery = urlencode(implode(', ', $addressParts));

    $tab = request('tab', 'info');
@endphp
    @include('layouts.header')
<div class="container mt-5">

    {{-- КНОПКА НАЗАД --}}
    <div class="mb-4">

                        <div class="mb-3">
                <a href="{{ route('doctors.index') }}" class="btn btn-outline-primary"
           title="Вернутся к каталогу всех врачей города">
                    <img src="{{ asset('storage/icon/button/arrow-back.svg') }}" width="22" alt="paw">
                    В каталог
                </a>
            </div>
    </div>

    {{-- ШАПКА --}}
    <div class="d-flex align-items-center flex-wrap mb-4">
        <img src="{{ $photo }}"
             style="width:90px;height:90px;border-radius:10px;object-fit:cover"
             class="me-3">

        <div>
            <h1 class="fw-bold m-0 d-flex align-items-center gap-2">
                {{ $doctor->name }}

                @if($doctor->exotic_animals)
                    <span class="badge bg-warning text-dark" title="Экзотические животные">🦎</span>
                @endif
            </h1>

            <div class="text-muted">
                {{ $doctor->specialization }}
            </div>
        </div>
    </div>

    {{-- ТАБЫ --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'info' ? 'active' : '' }}"  title="Просмотреть общую информацию" href="?tab=info">Информация</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'contacts' ? 'active' : ''  }}" title="Просмотреть контакты" href="?tab=contacts">Контакты</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'services' ? 'active' : ''  }}" title="Посмотреть перечь услуге которые оказывает данный специалист" href="?tab=services">Услуги</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'reviews' ? 'active' : ''  }}" title="Прочитать отзывы" href="?tab=reviews">Отзывы</a>
        </li>
    </ul>

    <div class="row">
        <div class="col-lg-8">
            @if($tab === 'info')
                @include('pages.doctors.tabs.info', ['doctor' => $doctor])
            @endif

            @if($tab === 'contacts')
                @include('pages.doctors.tabs.contacts', ['doctor' => $doctor])
            @endif

            @if($tab === 'services')
                @include('pages.doctors.tabs.services', ['doctor' => $doctor])
            @endif

            @if($tab === 'reviews')
                @include('pages.doctors.tabs.reviews', ['clinic_id' => $doctor->clinic_id])
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <img src="{{ $photo }}"
                         style="width:100%;max-width:280px;border-radius:10px;object-fit:cover">
                </div>
            </div>
        </div>
    </div>
</div>
<footer class="footer-fullwidth mt-auto w-100">
    @include('layouts.footer')
</footer>
@endsection
