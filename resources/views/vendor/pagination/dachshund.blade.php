{{--
    Кастомная пагинация «такса» — только для списков отзывов.
    Подключается точечно: {{ $reviews->links('vendor.pagination.dachshund') }}
    Не трогает дефолтную Bootstrap-5 пагинацию каталогов/объявлений.

    Картинка: public/images/pagination/dachshund.webp (1400×401, прозрачный фон).
    Ряд с номерами страниц позиционируется поверх спины таксы по проценту
    высоты картинки (подобрано по самой картинке — голова слева "назад",
    хвост справа "вперёд").
--}}
@if ($paginator->hasPages())
    <nav aria-label="Навигация по страницам отзывов">
        <div class="dachshund-pagination">
            <ul class="dachshund-pagination__row">

                {{-- Предыдущая страница --}}
                @if ($paginator->onFirstPage())
                    <li class="dachshund-pagination__item disabled">
                        <span class="dachshund-pagination__link" aria-hidden="true">‹</span>
                    </li>
                @else
                    <li class="dachshund-pagination__item">
                        <a class="dachshund-pagination__link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Предыдущая страница">‹</a>
                    </li>
                @endif

                {{-- Номера страниц --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="dachshund-pagination__dots" aria-hidden="true">{{ $element }}</li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="dachshund-pagination__item active" aria-current="page">
                                    <span class="dachshund-pagination__link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="dachshund-pagination__item">
                                    <a class="dachshund-pagination__link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Следующая страница --}}
                @if ($paginator->hasMorePages())
                    <li class="dachshund-pagination__item">
                        <a class="dachshund-pagination__link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Следующая страница">›</a>
                    </li>
                @else
                    <li class="dachshund-pagination__item disabled">
                        <span class="dachshund-pagination__link" aria-hidden="true">›</span>
                    </li>
                @endif

            </ul>
        </div>
    </nav>

    <style>
        .dachshund-pagination {
            position: relative;
            width: 100%;
            max-width: 640px;
            margin: 28px auto 12px;
            aspect-ratio: 1400 / 401;
            background-image: url('{{ asset('images/pagination/dachshund.webp') }}');
            background-size: 100% 100%;
            background-repeat: no-repeat;
        }
        .dachshund-pagination__row {
            position: absolute;
            top: 47%;
            left: 9%;
            right: 5%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: clamp(2px, 1.1vw, 7px);
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .dachshund-pagination__link {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: clamp(20px, 4vw, 28px);
            height: clamp(20px, 4vw, 28px);
            padding: 0 3px;
            border-radius: 50%;
            background: #fdf3e2;
            border: 2px solid #4a2f1e;
            color: #4a2f1e;
            font-weight: 700;
            font-size: clamp(11px, 2.4vw, 13px);
            text-decoration: none;
            line-height: 1;
            transition: transform .15s ease, background-color .15s ease, color .15s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,.15);
        }
        a.dachshund-pagination__link:hover {
            background: #ffcf80;
            transform: translateY(-2px);
        }
        .dachshund-pagination__item.active .dachshund-pagination__link {
            background: #ff8c00;
            border-color: #ff8c00;
            color: #fff;
        }
        .dachshund-pagination__item.disabled .dachshund-pagination__link {
            opacity: .35;
            cursor: default;
        }
        .dachshund-pagination__dots {
            color: #4a2f1e;
            font-weight: 700;
            padding: 0 1px;
            font-size: clamp(11px, 2.4vw, 13px);
        }

        @media (max-width: 420px) {
            .dachshund-pagination__row { left: 11%; right: 6%; gap: 2px; }
        }
    </style>
@endif
