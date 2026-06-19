@extends('layouts.app')

<title>Кабинет — {{ $specialist->name ?? '' }} — Зверозор</title>

@section('content')
@include('layouts.header')

@php
    $entity   = $specialist;
    $entityId = $specialist->id;
    $type     = 'specialist';
    $title    = 'Кабинет — ' . ($entity->name ?? '');
@endphp

<div class="container my-4" style="max-width: 1100px;">

    {{-- Флэш-сообщения --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- Левая навигация --}}
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 sticky-top p-0" style="top:16px;">

                <div class="text-center p-4 border-bottom">
                    @php $photoField = in_array('specialist', ['doctor','specialist']) ? 'photo' : 'logo'; @endphp
                    @if(!empty($entity->{$photoField}))
                        <img src="{{ Storage::url($entity->{$photoField}) }}"
                             class="{{ in_array('specialist', ['doctor','specialist']) ? 'rounded-circle' : 'rounded-3' }} mb-2"
                             style="width:80px;height:80px;object-fit:cover;">
                    @else
                        <div class="rounded-3 bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mb-2 mx-auto"
                             style="width:80px;height:80px;font-size:32px;">
                            {{ ['clinic'=>'🏥','organization'=>'🏢','doctor'=>'👨‍⚕️','specialist'=>'🩺']['specialist'] }}
                        </div>
                    @endif
                    <div class="fw-bold text-dark" style="font-size:14px;">{{ $entity->name ?? '' }}</div>
                    <span class="badge bg-success bg-opacity-10 text-success mt-1" style="font-size:11px;">✓ Подтверждён</span>
                </div>

                <nav class="p-2">
                    @php $activeTab = request('tab', 'info'); @endphp
                    @foreach(['info' => ['icon'=>'📋','label'=>'Основная информация'], 'photos' => ['icon'=>'📷','label'=>'Фотографии'], 'services' => ['icon'=>'💊','label'=>'Услуги и цены']] as $key => $tab)
                        <a href="?tab={{ $key }}"
                           class="d-flex align-items-center gap-2 px-3 py-2 rounded-2 mb-1 text-decoration-none {{ $activeTab === $key ? 'bg-primary text-white fw-medium' : 'text-secondary' }}"
                           style="font-size:14px;">
                            <span>{{ $tab['icon'] }}</span> <span>{{ $tab['label'] }}</span>
                        </a>
                    @endforeach
                    <hr class="my-2 opacity-25">
                    @if(!empty($entity->slug))
                        <a href="{{ url('specialists/' . $entity->slug) }}" target="_blank"
                           class="d-flex align-items-center gap-2 px-3 py-2 text-decoration-none text-muted"
                           style="font-size:13px;">
                            🌐 Публичная страница
                        </a>
                    @endif
                </nav>
            </div>
        </div>

        {{-- Контент --}}
        <div class="col-lg-9">
            @include('pages.owner._tabs', compact('entity', 'entityId', 'type', 'photos', 'services'))
        </div>

    </div>
</div>

@include('layouts.footer')
@endsection
