<style>
    .fade:not(.show) {
        opacity: 1 !important;
    }
    /* Стили для алфавитной навигации (лапки) */
    .paw-circle {
        position: relative;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .paw-icon {
        width: 100%;
        height: 100%;
        position: absolute;
    }
    .paw-letter {
        position: relative;
        z-index: 1;
        font-weight: bold;
        color: #fff;
        font-size: 0.9rem;
        margin-top: -2px;
    }
</style>

<div class="tab-pane fade" id="services" role="tabpanel">
    @php
        /** 
         * Добавляем $organization в список проверяемых моделей.
         * Это позволит использовать один и тот же шаблон во всех разделах сайта.
         */
        $currentModel = $organization ?? $clinic ?? $doctor ?? $specialist ?? null;

        if (!$currentModel) {
            $pricesCollection = collect();
        } else {
            // Загружаем цены с жадной загрузкой сервисов для оптимизации (Eager Loading)
            $pricesCollection = $currentModel->prices()->with('service')->get();
        }

        // Группируем по специализации сервиса
        $grouped = $pricesCollection->groupBy(function($priceItem) {
            return $priceItem->service->specialization ?? 'Общие услуги';
        })->sortKeys();

        // Сортировка услуг по алфавиту внутри каждой группы
        foreach ($grouped as $key => $group) {
            $grouped[$key] = $group->sortBy(fn($item) => $item->service->name ?? '');
        }

        // Подготовка букв для алфавитного меню
        $letters = collect($grouped->keys())
            ->map(fn($key) => mb_strtoupper(mb_substr($key, 0, 1)))
            ->unique()
            ->sort()
            ->values();
    @endphp

    @if($grouped->isNotEmpty())
        {{-- Алфавитный указатель --}}
        <div class="mb-4 d-flex flex-wrap gap-2 justify-content-start">
            @foreach($letters as $letter)
                <a href="#letter-{{ $letter }}" class="paw-link text-decoration-none shadow-sm rounded-circle">
                    <div class="paw-circle">
                        <img src="{{ asset('storage/icon/alphabet/letter_icon.png') }}" class="paw-icon" alt="paw">
                        <span class="paw-letter">{{ $letter }}</span>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Списки услуг по группам --}}
        @foreach($grouped as $specialization => $items)
            @php $anchor = mb_strtoupper(mb_substr($specialization, 0, 1)); @endphp
            <div id="letter-{{ $anchor }}" class="mb-5 specialization-block">
                <h5 class="fw-semibold text-primary border-bottom pb-2 mb-3 d-flex align-items-center">
                    <i class="bi bi-tag-fill me-2"></i> {{ $specialization }}
                </h5>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle shadow-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 70%">Название услуги</th>
                                <th style="width: 30%" class="text-center">Стоимость</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                @if($item->service)
                                    <tr>
                                        <td class="fw-medium text-dark">{{ $item->service->name }}</td>
                                        <td class="text-center fw-bold text-primary">
                                            {{ $item->price ? number_format($item->price, 0, ',', ' ') . ' ' . ($item->currency ?? '₽') : '—' }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @else
        <div class="text-center py-5">
            <img src="{{ asset('storage/icon/empty_list.svg') }}" alt="Empty" style="width: 80px; opacity: 0.5;">
            <p class="text-muted mt-3">У данной организации прайс-лист пока не заполнен.</p>
        </div>
    @endif
</div>