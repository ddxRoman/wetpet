{{--
    Акции под названием организации/клиники в результатах поиска.
    Показываются только у карточек с активным пакетом «Выделение» (это учитывает Promotion::active()).
    Параметр: $entity (Clinic | Organization)
--}}
@php
    $searchPromos = $entity->promotions()->active()->orderBy('id')->limit(2)->get();
@endphp

@if($searchPromos->isNotEmpty())
    <div class="mb-1">
        @foreach($searchPromos as $promo)
            <div class="d-inline-flex align-items-center gap-1 me-2 px-2 py-1 rounded-2"
                 style="background:#fff3e0;border:1px solid #ffcc80;font-size:12px;">
                <span>🏷️</span>
                <span class="fw-medium" style="color:#92400e;">{{ $promo->title }}</span>
                @if($promo->new_price)
                    <span class="fw-bold" style="color:#16a34a;white-space:nowrap;">{{ number_format($promo->new_price, 0, '.', ' ') }} ₽</span>
                @elseif($promo->badge)
                    <span class="badge rounded-pill" style="background:#dc3545;font-size:10px;">{{ $promo->badge }}</span>
                @endif
            </div>
        @endforeach
    </div>
@endif
