{{--
    Компактный значок акции для карточек в каталоге.
    Использование: @include('partials._promotions-badge', ['entity' => $specialist])
--}}
@php
    $firstPromo = $entity->promotions()->active()->first();
@endphp

@if($firstPromo)
<div class="promo-badge-compact d-flex align-items-center gap-1 mt-2 px-2 py-1 rounded-2"
     style="background:#fff3e0;border:1px solid #ffcc80;font-size:12px;"
     title="{{ $firstPromo->title }}">
    <span style="color:#dc3545;font-weight:700;">🏷️</span>
    <span class="text-truncate fw-medium" style="color:#92400e;max-width:120px;">
        {{ $firstPromo->title }}
    </span>
    @if($firstPromo->new_price)
    <span class="ms-auto fw-bold" style="color:#16a34a;white-space:nowrap;">
        {{ number_format($firstPromo->new_price, 0, '.', ' ') }} ₽
    </span>
    @elseif($firstPromo->badge)
    <span class="ms-auto badge rounded-pill" style="background:#dc3545;font-size:10px;">
        {{ $firstPromo->badge }}
    </span>
    @endif
</div>
@endif
