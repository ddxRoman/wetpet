@extends('layouts.app')
@include('layouts.header')
@section('content')
<div class="container py-5">
    <h1 class="mb-4">Результаты поиска: «{{ $query }}»</h1>

    <div class="row">
        {{-- Список результатов --}}
        <div class="col-lg-10">
            @php $hasAny = false; @endphp

            {{-- Блок Клиник --}}
            @if($results['clinics']->count() > 0)
                @php $hasAny = true; @endphp
                <h3 class="mt-4 text-primary">🏥 Клиники</h3>
                @foreach($results['clinics'] as $clinic)
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body d-flex align-items-center">
                            <img src="{{ $clinic->logo ? Storage::url($clinic->logo) : asset('storage/clinics/logo/default-clinic.webp') }}" style="width: 80px; height: 80px; object-fit: cover;" class="rounded" alt="">
                            <div class="ms-3">
                                <h5 class="mb-1"><a href="/clinics/{{ $clinic->slug }}">{{ $clinic->name }}</a></h5>
                                <p class="text-muted mb-0">{{ $clinic->city }}, {{ $clinic->street }} {{ $clinic->house }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- Блок Врачей --}}
            @if($results['doctors']->count() > 0)
                @php $hasAny = true; @endphp
                <h3 class="mt-4 text-info">🩺 Врачи</h3>
                <div class="row">
                    @foreach($results['doctors'] as $doctor)
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body text-center">
                                    <img src="{{ $doctor->photo ? Storage::url($doctor->photo) : asset('storage/doctors/default-doctor.webp') }}" class="rounded-circle mb-2" style="width: 100px; height: 100px; object-fit: cover;">
                                    <h5><a href="/doctors/{{ $doctor->slug }}" class="text-decoration-none">{{ $doctor->name }}</a></h5>
                                    <p class="small text-muted">{{ $doctor->specialization }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Блок Пород --}}
            @if($results['animals']->count() > 0)
                @php $hasAny = true; @endphp
                <h3 class="mt-4" style="color: #d63384;">🐾 Породы животных</h3>
                <div class="list-group">
                    @foreach($results['animals'] as $animal)
                        <a href="/animals/{{ $animal->species_slug }}/{{ $animal->breed_slug }}" class="list-group-item list-group-item-action">
                            <strong>{{ $animal->breed }}</strong> — <span class="text-muted">{{ $animal->species }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            @if(!$hasAny)
                <div class="alert alert-light text-center py-5 shadow-sm">
                    <h3>Ничего не найдено 😔</h3>
                    <p>Попробуйте изменить запрос или поискать в других категориях.</p>
                    <a href="/" class="btn btn-primary">На главную</a>
                </div>
            @endif
        </div>

        {{-- Боковая панель (Реклама) --}}
        <!-- <div class="col-lg-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h5>Поиск по категориям</h5>
                    <ul class="list-unstyled">
                        <li><a href="/clinics" class="text-decoration-none">Все клиники</a></li>
                        <li><a href="/doctors" class="text-decoration-none">Найти врача</a></li>
                        <li><a href="/organizations" class="text-decoration-none">Организации</a></li>
                    </ul>
                </div>
            </div>
        </div> -->
    </div>
</div>
@endsection