{{--
    Виджет акций для show-страниц организации/специалиста.
    Использование: @include('partials._promotions-widget', ['entity' => $organization])
--}}
@php
    $activePromos = $entity->promotions()->active()->get();
@endphp

@if($activePromos->isNotEmpty())
<div class="promo-widget mb-4">
    <div class="promo-widget-header d-flex align-items-center gap-2 mb-3">
        <span style="font-size:20px;">🏷️</span>
        <h6 class="fw-bold mb-0" style="color:#dc3545;">Акции и спецпредложения</h6>
        <span class="badge rounded-pill ms-auto" style="background:#dc3545;font-size:11px;">
            {{ $activePromos->count() }} {{ $activePromos->count() === 1 ? 'акция' : ($activePromos->count() < 5 ? 'акции' : 'акций') }}
        </span>
    </div>

    <div class="row g-3">
        @foreach($activePromos as $promo)
        <div class="{{ $activePromos->count() === 1 ? 'col-12' : 'col-md-6' }}">
            <div class="promo-card h-100 rounded-3 p-3 position-relative"
                 style="background:linear-gradient(135deg,#fff8f0 0%,#fff3e0 100%);border:1.5px solid #ffcc80;">

                {{-- Бейдж --}}
                @if($promo->badge)
                <span class="position-absolute top-0 end-0 m-2 badge rounded-pill"
                      style="background:#dc3545;font-size:11px;font-weight:700;">
                    {{ $promo->badge }}
                </span>
                @endif

                {{-- Название --}}
                <div class="fw-bold mb-1" style="font-size:15px;color:#1f2937;padding-right:50px;">
                    {{ $promo->title }}
                </div>

                {{-- Описание --}}
                @if($promo->description)
                <div class="text-muted mb-2" style="font-size:13px;line-height:1.4;">
                    {{ $promo->description }}
                </div>
                @endif

                {{-- Цены --}}
                @if($promo->old_price || $promo->new_price)
                <div class="d-flex align-items-center gap-2 mt-auto">
                    @if($promo->old_price)
                    <span style="text-decoration:line-through;color:#9ca3af;font-size:13px;">
                        {{ number_format($promo->old_price, 0, '.', ' ') }} ₽
                    </span>
                    @endif
                    @if($promo->new_price)
                    <span style="color:#16a34a;font-weight:700;font-size:18px;">
                        {{ number_format($promo->new_price, 0, '.', ' ') }} ₽
                    </span>
                    @endif
                    @if($promo->old_price && $promo->new_price)
                    @php $discount = round((1 - $promo->new_price / $promo->old_price) * 100) @endphp
                    <span class="badge rounded-pill ms-1" style="background:#dcfce7;color:#16a34a;font-size:11px;">
                        −{{ $discount }}%
                    </span>
                    @endif
                </div>
                @endif

                {{-- Срок --}}
                @if($promo->expires_at)
                <div class="mt-2" style="font-size:11px;color:#9ca3af;">
                    ⏳ Акция до {{ $promo->expires_at->format('d.m.Y') }}
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<style>
.promo-widget { }
.promo-card { transition: transform .18s, box-shadow .18s; }
.promo-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(220,53,69,.12); }
</style>
@endif
