@extends('layouts.app')

@section('content')
@include('layouts.header')

<div class="container my-5" style="max-width: 900px;">

    {{-- Хлебные крошки --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none">Главная</a></li>
            <li class="breadcrumb-item"><a href="{{ route('legal/glossary') }}" class="text-decoration-none">Словарь терминов</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $term->term }}</li>
        </ol>
    </nav>

    <article class="card border-0 shadow-sm rounded-3 p-4 p-md-5">
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
            <span class="badge bg-primary bg-opacity-10 text-primary">{{ $term->letter }}</span>
            @if($term->category)
                <a href="{{ route('legal/glossary') }}?category={{ urlencode($term->category) }}"
                   class="badge bg-secondary bg-opacity-75 text-decoration-none">{{ $term->category_label !== '—' ? $term->category_label : $term->category }}</a>
            @endif
        </div>

        <h1 class="fw-bold text-dark mb-4" style="font-size: 2rem;">{{ $term->term }}</h1>

        <div class="text-secondary" style="line-height: 1.8; font-size: 1.05rem;">
            {!! nl2br(e($term->definition)) !!}
        </div>
    </article>

    @if($related->isNotEmpty())
        <div class="mt-5">
            <h2 class="fs-5 fw-semibold mb-3">Другие термины{{ $term->category ? ' из категории «' . ($term->category_label !== '—' ? $term->category_label : $term->category) . '»' : '' }}</h2>
            <div class="d-flex flex-wrap gap-2">
                @foreach($related as $item)
                    <a href="{{ route('glossary.show', $item->slug) }}"
                       class="btn btn-sm btn-outline-primary rounded-pill">{{ $item->term }}</a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('legal/glossary') }}" class="btn btn-outline-secondary rounded-pill btn-sm">← Все термины</a>
    </div>
</div>

@include('layouts.footer')
@endsection
