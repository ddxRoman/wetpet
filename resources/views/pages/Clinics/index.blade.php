@extends('layouts.clinics_catalog')

@section('title', 'Каталог ветеринарных клиник')

@section('content')
<div class="container py-5">
    <h1 class="mb-4 text-center">Каталог ветеринарных клиник
        @if(!empty($selectedCity))
        <small class="text-muted d-block fs-6">Город: {{ $selectedCity }}</small>
        @endif
    </h1>
    <div class="row g-4">
        @foreach ($clinics as $clinic)
            <div class="col-md-4 col-12">
                <a href="{{ route('clinics.show', $clinic->id) }}" class="text-decoration-none text-reset">
                    <div class="card h-100 shadow-sm hover-shadow position-relative transition">

                        {{-- ⭐ Рейтинг — теперь в левом верхнем углу --}}
                        <div class="rating-badge position-absolute top-0 start-0 m-2 px-2 py-1 bg-warning text-dark rounded-pill d-flex align-items-center">
                            ⭐ <span class="ms-1 fw-semibold">4.1</span>
                        </div>

                        @php
                            $logo = !empty($clinic->logo)
                                ? asset('storage/' . $clinic->logo)
                                : asset('storage/clinics/logo/default.webp');
                        @endphp

                        <img src="{{ $logo }}" class="card-img-top object-fit-contain p-3" alt="{{ $clinic->name }}">

                        <div class="card-body">
                            <h5 class="card-title">{{ $clinic->name }}</h5>
                            <p class="card-text mb-2">
                                {{ $clinic->country }}, {{ $clinic->city }}, {{ $clinic->street }} {{ $clinic->house }}
                            </p>
                            @if(!empty($clinic->schedule))
                                <p class="text-muted mb-0">
                                    График: {{ $clinic->schedule }}
                                </p>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>


@endsection
