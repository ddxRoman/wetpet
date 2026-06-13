@extends('layouts.app')

<title>Часто задаваемые вопросы — Зверозор</title>

@section('content')
@include('layouts.header')

<div class="container my-5" style="max-width: 900px;">

    <div class="text-center mb-5">
        <h2 class="fw-bold text-dark mb-2">Часто задаваемые вопросы</h2>
        <p class="text-muted small">Ответы на самые популярные вопросы о сервисе</p>
    </div>


    {{-- Список вопросов --}}
    @if($faqs->isEmpty())
        <div class="text-center text-muted py-5">
            <p class="mb-0">Вопросов пока нет.</p>
        </div>
    @else
        @if($currentCategory && $faqs->isNotEmpty())
            <h5 class="text-muted fw-normal mb-3 small text-uppercase tracking-wide">
                {{ $categoryLabels[$currentCategory] ?? '' }}
            </h5>
        @endif

        <div class="accordion accordion-flush shadow-sm rounded-3 overflow-hidden" id="faqAccordion">
            @foreach($faqs as $i => $faq)
                <div class="accordion-item border-0 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <h2 class="accordion-header">
                        <button
                            class="accordion-button {{ $i !== 0 ? 'collapsed' : '' }} fw-medium"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#faq-{{ $faq->id }}"
                            aria-expanded="{{ $i === 0 ? 'true' : 'false' }}"
                            aria-controls="faq-{{ $faq->id }}"
                        >
                            {{ $faq->question }}
                        </button>
                    </h2>
                    <div id="faq-{{ $faq->id }}"
                         class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}"
                         data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-secondary" style="line-height: 1.7;">
                            {!! nl2br(e($faq->answer)) !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Блок "Не нашли ответ?" --}}
    <div class="mt-5 p-4 rounded-3 bg-light border text-center">
        <p class="fw-medium text-dark mb-1">Не нашли ответ на свой вопрос?</p>
        <p class="text-muted small mb-3">Напишите нам, и мы ответим в ближайшее время</p>
        <a href="{{ route('legal/contacts') }}" class="btn btn-primary rounded-pill px-4">
            Написать нам
        </a>
    </div>

</div>

@include('layouts.footer')
@endsection
