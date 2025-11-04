@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h1 class="mb-4 text-center">Каталог ветеринарных клиник</h1>

    <div class="row g-4">
        @foreach ($clinics as $clinic)
            <div class="col-md-4 col-12">
                <div class="card h-100 shadow-sm">
                    @if(!empty($clinic->logo))
                        <img src="{{ $clinic->logo }}" class="card-img-top" alt="{{ $clinic->name }}">
                    @endif
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $clinic->name }}</h5>
                        <p class="card-text mb-2">
                            {{ $clinic->country }}, {{ $clinic->city }},
                            {{ $clinic->street }} {{ $clinic->house }}
                        </p>
                        @if(!empty($clinic->schedule))
                            <p class="text-muted mb-3">График: {{ $clinic->schedule }}</p>
                        @endif
                        <a href="{{ route('clinics.show', $clinic->id) }}" class="btn btn-primary mt-auto">
                            Подробнее
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
