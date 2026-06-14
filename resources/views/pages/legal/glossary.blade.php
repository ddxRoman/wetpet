@extends('layouts.app')

<title>Словарь терминов — Зверозор</title>

@section('content')
@include('layouts.header')

<div class="container my-5" style="max-width: 900px;">

    <div class="text-center mb-5">
        <h2 class="fw-bold text-dark mb-2">Словарь терминов</h2>
        <p class="text-muted small">Ветеринарные и юридические термины, используемые на платформе</p>
    </div>

    @if($terms->isEmpty())
        <div class="text-center text-muted py-5">
            <p class="mb-0">Термины пока не добавлены.</p>
        </div>
    @else

        {{-- Алфавитная навигация --}}
        <div class="d-flex flex-wrap gap-1 justify-content-center mb-5">
            @foreach($letters as $letter)
                <a href="#letter-{{ $letter }}"
                   class="btn btn-sm btn-outline-primary rounded-circle fw-bold"
                   style="width: 38px; height: 38px; line-height: 24px; padding: 0;">
                    {{ $letter }}
                </a>
            @endforeach
        </div>

        {{-- Термины сгруппированные по буквам --}}
        @foreach($grouped as $letter => $items)
            <div class="mb-5" id="letter-{{ $letter }}">

                {{-- Буква-разделитель --}}
                <div class="d-flex align-items-center mb-3">
                    <span class="fw-bold text-primary fs-4 me-3" style="min-width: 36px;">{{ $letter }}</span>
                    <hr class="flex-grow-1 m-0 opacity-25">
                </div>

                {{-- Карточки терминов --}}
                <div class="row g-3">
                    @foreach($items as $term)
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-3 p-4">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded-2 px-2 py-1"
                                         style="font-size: 0.75rem; font-weight: 600; white-space: nowrap;">
                                        {{ mb_strtoupper($term->letter) }}
                                    </div>
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1">{{ $term->term }}</h5>
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

        {{-- Кнопка наверх --}}
        <div class="text-center mt-4">
            <a href="#" class="btn btn-outline-secondary rounded-pill px-4 btn-sm">
                ↑ Наверх
            </a>
        </div>

    @endif

</div>

@include('layouts.footer')
@endsection
