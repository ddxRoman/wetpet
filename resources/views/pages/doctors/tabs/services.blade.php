<style>
    .fade:not(.show) {
        opacity: 1 !important;
    }
</style>

<div class="tab-pane fade" id="services" role="tabpanel">
    @php
        // 1. Безопасно определяем клинику (от доктора или напрямую)
        $currentClinic = $clinic ?? ($doctor->clinic ?? null);
        
        $grouped = collect();
        $letters = collect();

        // 2. Выполняем логику только если клиника существует
        if ($currentClinic) {
            $pricesCollection = $currentClinic->prices()->with('service')->get();

            // Группировка
            $grouped = $pricesCollection->groupBy(function($priceItem) {
                return $priceItem->service->specialization ?? 'Общие услуги';
            })->sortKeys();

            // Сортировка внутри групп
            foreach ($grouped as $key => $group) {
                $grouped[$key] = $group->sortBy(fn($item) => $item->service->name ?? '');
            }

            // Буквы для навигации
            $letters = collect($grouped->keys())
                ->map(fn($key) => mb_strtoupper(mb_substr($key, 0, 1)))
                ->unique()
                ->sort()
                ->values();
        }
    @endphp

    {{-- Проверяем наличие сгруппированных данных --}}
    @if($grouped->isNotEmpty())
        {{-- Алфавитный указатель --}}
        <div class="mb-4 d-flex flex-wrap gap-2 justify-content-start">
            @foreach($letters as $letter)
                <a href="#letter-{{ $letter }}" class="paw-link text-decoration-none">
                    <div class="paw-circle">
                        <img src="{{ asset('storage/icon/alphabet/letter_icon.png') }}" class="paw-icon" alt="paw">
                        <span class="paw-letter">{{ $letter }}</span>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Списки услуг по специализациям --}}
        @foreach($grouped as $specialization => $items)
            @php $anchor = mb_strtoupper(mb_substr($specialization, 0, 1)); @endphp
            <div id="letter-{{ $anchor }}" class="mb-5 specialization-block">
                <h5 class="fw-semibold text-primary border-bottom pb-2 mb-3">
                    {{ $specialization }}
                </h5>

                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60%">Название услуги</th>
                            <th style="width: 40%">Стоимость</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            @if($item->service)
                                <tr>
                                    <td>{{ $item->service->name }}</td>
                                    <td>
                                        {{ $item->price ? number_format($item->price, 0, ',', ' ') . ' ' . ($item->currency ?? '₽') : '—' }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @else
        {{-- Заглушка, если услуг нет или клиника не найдена --}}
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="bi bi-card-list text-secondary" style="font-size: 2.5rem;"></i>
            </div>
            <h5 class="text-secondary">Услуги не указаны</h5>
            <p class="text-muted">Для данного специалиста прайс-лист временно пуст или не заполнен.</p>
        </div>
    @endif
</div>