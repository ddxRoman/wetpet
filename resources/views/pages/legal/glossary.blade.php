@extends('layouts.app')

<title>Словарь терминов — Зверозор</title>

@section('content')
@include('layouts.header')

@php $categoryLabels = \App\Models\GlossaryTerm::categories(); @endphp

<div class="container my-5" style="max-width: 1100px;">

    <div class="text-center mb-4">
        <h2 class="fw-bold text-dark mb-2">Словарь терминов</h2>
        <p class="text-muted small">Ветеринарные и юридические термины, используемые на платформе</p>
    </div>

    @if($terms->isEmpty() && !$currentCategory)
        <div class="text-center text-muted py-5">
            <p class="mb-0">Термины пока не добавлены.</p>
        </div>
    @else

        {{-- Алфавитная навигация вверху --}}
        @if($letters->isNotEmpty())
            <div class="d-flex flex-wrap gap-1 justify-content-center mb-5">
                @foreach($letters as $letter)
                    <a href="#letter-{{ $letter }}"
                       class="btn btn-sm btn-outline-primary rounded-circle fw-bold"
                       style="width: 38px; height: 38px; line-height: 24px; padding: 0;">
                        {{ $letter }}
                    </a>
                @endforeach
            </div>
        @endif

        <div class="row g-4">

            {{-- ───── Левая колонка: категории ───── --}}
            <div class="col-lg-3 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 p-3 sticky-top" style="top: 20px;">
                    <p class="text-muted small fw-semibold text-uppercase mb-2" style="letter-spacing: .05em;">Категории</p>
                    <nav class="d-flex flex-column gap-1">
                        <a href="{{ route('legal/glossary') }}"
                           class="nav-link rounded-2 px-3 py-2 {{ !$currentCategory ? 'bg-primary text-white fw-medium' : 'text-secondary' }}"
                           style="font-size: 0.9rem;">
                            Все термины
                        </a>
                        @foreach($categories as $category)
                            <a href="{{ route('legal/glossary') }}?category={{ $category }}"
                               class="nav-link rounded-2 px-3 py-2 {{ $currentCategory === $category ? 'bg-primary text-white fw-medium' : 'text-secondary' }}"
                               style="font-size: 0.9rem;">
                                {{ $category }}
                            </a>
                        @endforeach
                    </nav>
                </div>
            </div>

            {{-- ───── Правая колонка: термины ───── --}}
            <div class="col-lg-9 col-md-8">

                {{-- Заголовок активной категории --}}
                @if($currentCategory)
                    <div class="mb-4 d-flex align-items-center gap-3">
                        <span class="fw-semibold text-dark">{{ $currentCategory }}</span>
                        <span class="text-muted small">{{ $terms->count() }} {{ trans_choice('термин|термина|терминов', $terms->count()) }}</span>
                        <a href="{{ route('legal/glossary') }}" class="btn btn-sm btn-outline-secondary rounded-pill ms-auto">× Сбросить</a>
                    </div>
                @endif

                {{-- Пусто по фильтру --}}
                @if($terms->isEmpty())
                    <div class="text-center text-muted py-5">
                        <p class="mb-2">По выбранной категории терминов пока нет.</p>
                        <a href="{{ route('legal/glossary') }}" class="btn btn-sm btn-outline-primary rounded-pill">
                            Показать все термины
                        </a>
                    </div>
                @else

                    {{-- Термины сгруппированные по букве --}}
                    @foreach($grouped as $letter => $items)
                        <div class="mb-5" id="letter-{{ $letter }}">

                            <div class="d-flex align-items-center mb-3">
                                <span class="fw-bold text-primary fs-4 me-3" style="min-width: 36px;">{{ $letter }}</span>
                                <hr class="flex-grow-1 m-0 opacity-25">
                            </div>

                            <div class="row g-3">
                                @foreach($items as $term)
                                    <div class="col-12">
                                        <div class="card border-0 shadow-sm rounded-3 p-4">
                                            <div class="d-flex align-items-start gap-3">

                                                <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded-2 px-2 py-1"
                                                     style="font-size: 0.75rem; font-weight: 600; white-space: nowrap;">
                                                    {{ $letter }}
                                                </div>

                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                                        <h5 class="fw-bold text-dark mb-0">{{ $term->term }}</h5>
                                                        @if($term->category)
                                                            <span class="badge bg-secondary bg-opacity-75"
                                                                  style="font-size: 0.7rem;">
                                                                {{ $term->category }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <p class="text-secondary mb-0" style="line-height: 1.7; font-size: 0.95rem;">
                                                        {{ $term->definition }}
                                                    </p>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    @endforeach

                    <div class="text-center mt-2">
                        <a href="#" class="btn btn-outline-secondary rounded-pill px-4 btn-sm">↑ Наверх</a>
                    </div>

                @endif
            </div>
        </div>
    @endif

</div>

@include('layouts.footer')
@endsection
