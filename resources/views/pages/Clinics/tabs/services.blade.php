<style>
    .fade:not(.show) {
    opacity: 1 !important;
}
</style>

<div class="tab-pane fade" id="services" role="tabpanel">

    @php
        // Получаем цены через полиморфную связь
        $pricesCollection = $clinic->prices()->with('service')->get();

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
    @endphp

    @if($grouped->isNotEmpty())
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
                                        {{ $item->price ? number_format($item->price, 0, ',', ' ') . ' ' . $item->currency : '—' }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @else
        <p class="text-muted text-center py-4">Прайс-лист временно пуст.</p>
    @endif
</div>