@extends('layouts.clinics_catalog')

@section('title', 'Каталог ветеринарных клиник')

@section('content')
<div class="container py-5">
    <h1 class="mb-4 text-center">Каталог ветеринарных клиник</h1>

    <div class="row g-4">
        @foreach ($clinics as $clinic)
            <div class="col-md-4 col-12">
                <a href="{{ route('clinics.show', $clinic->id) }}" class="text-decoration-none text-reset">
                    <div class="card h-100 shadow-sm hover-shadow transition">
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

<style>
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
}
.card-img-top {
    height: 180px;
    object-fit: contain;
    background-color: #f8f9fa;
    border-bottom: 1px solid #eee;
}
</style>
@endsection
