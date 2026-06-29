@php
    $activePromos = $entity->promotions()->active()->get();
@endphp

@if($activePromos->isNotEmpty())
<div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
    <h6 class="fw-bold mb-3">🏷️ Акции и спецпредложения</h6>
    <div class="d-flex flex-column gap-3">
        @foreach($activePromos as $promo)
        <div class="rounded-3 p-3" style="background:#fff8f0;border:1px solid #ffe0b2;">
            <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                <div class="fw-semibold" style="font-size:14px;">{{ $promo->title }}</div>
                @if($promo->badge)
                    <span class="badge rounded-pill" style="background:#dc3545;font-size:11px;">{{ $promo->badge }}</span>
                @endif
            </div>
            @if($promo->description)
                <div class="text-muted mt-1" style="font-size:13px;">{{ $promo->description }}</div>
            @endif
            <div class="d-flex align-items-center justify-content-between mt-2 flex-wrap gap-2">
                <div>
                    @if($promo->old_price)
                        <span style="text-decoration:line-through;color:#999;font-size:13px;margin-right:4px;">
                            {{ number_format($promo->old_price, 0, '.', ' ') }} ₽
                        </span>
                        <span style="color:#28a745;font-weight:700;font-size:15px;">
                            {{ number_format($promo->new_price, 0, '.', ' ') }} ₽
                        </span>
                    @endif
                </div>
                @if($promo->expires_at)
                    <span class="text-muted" style="font-size:12px;">
                        До {{ $promo->expires_at->format('d.m.Y') }}
                    </span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
