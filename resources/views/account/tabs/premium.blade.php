@php
    $__premiumPlans   = config('premium.plans');
    $__monthPrice     = $__premiumPlans['month']['price'];
    $__yearPrice      = $__premiumPlans['year']['price'];
    $__yearSaving     = $__monthPrice * 12 - $__yearPrice;
    $__isPremium      = $user->isPremium();
    $__gatewayReady   = (bool) app(\App\Services\Premium\PremiumService::class)->gateway();
    $__lastPayments   = $user->premiumPayments()->where('status', 'paid')->latest('paid_at')->limit(5)->get();
@endphp

<div class="tab-content" id="premium" style="display:none;">
    <h2>Премиум</h2>

    @if(session('premium_success'))
        <div class="password-alert success">{{ session('premium_success') }}</div>
    @endif
    @if(session('premium_error'))
        <div class="password-alert error">{{ session('premium_error') }}</div>
    @endif

    {{-- Статус подписки --}}
    @if($__isPremium)
        <div class="premium-status premium-status--active">
            ⭐ Премиум активен до <strong>{{ $user->premium_until->format('d.m.Y') }}</strong>
        </div>
    @else
        <div class="premium-status">
            @if($user->premium_until)
                Подписка закончилась {{ $user->premium_until->format('d.m.Y') }}. Продлите её, чтобы вернуть возможности Премиум.
            @else
                У вас пока нет Премиум-подписки.
            @endif
        </div>
    @endif

    <h3 style="margin-top:20px;">Что даёт Премиум</h3>
    <ul class="premium-benefits">
        <li>📷 До {{ config('premium.limits.pet_photos.premium') }} фото у каждого питомца</li>
        <li>🐾 Отдельная страница для каждого питомца</li>
        <li>🖼 Несколько фото в отзыве (до {{ config('premium.limits.review_photos.premium') }})</li>
        <li>⭐ Доверенные пользователи и лента их отзывов</li>
    </ul>

    {{-- Тарифы --}}
    <h3 style="margin-top:20px;">{{ $__isPremium ? 'Продлить подписку' : 'Оформить подписку' }}</h3>

    <div class="premium-plans">
        <form method="POST" action="{{ route('premium.checkout') }}" class="premium-plan">
            @csrf
            <input type="hidden" name="plan" value="month">
            <div class="premium-plan__title">{{ $__premiumPlans['month']['label'] }}</div>
            <div class="premium-plan__price">{{ $__monthPrice }} ₽</div>
            <div class="premium-plan__hint">{{ $__monthPrice }} ₽ в месяц</div>
            <button type="submit" class="save-btn" @disabled(! $__gatewayReady)>Оплатить</button>
        </form>

        <form method="POST" action="{{ route('premium.checkout') }}" class="premium-plan premium-plan--best">
            @csrf
            <input type="hidden" name="plan" value="year">
            <div class="premium-plan__badge">Выгоднее на {{ $__yearSaving }} ₽</div>
            <div class="premium-plan__title">{{ $__premiumPlans['year']['label'] }}</div>
            <div class="premium-plan__price">{{ $__yearPrice }} ₽</div>
            <div class="premium-plan__hint">{{ round($__yearPrice / 12) }} ₽ в месяц</div>
            <button type="submit" class="save-btn" @disabled(! $__gatewayReady)>Оплатить</button>
        </form>
    </div>

    @unless($__gatewayReady)
        <p class="section-hint" style="margin-top:10px;">Оплата пока недоступна — мы подключаем платёжную систему.</p>
    @endunless

    @if($__isPremium)
        <p class="section-hint" style="margin-top:10px;">При продлении новый срок добавляется к текущему.</p>
    @endif

    @if($__lastPayments->isNotEmpty())
        <h3 style="margin-top:25px;">Последние платежи</h3>
        <ul class="premium-payments">
            @foreach($__lastPayments as $__pay)
                <li>{{ optional($__pay->paid_at)->format('d.m.Y') }} — {{ $__pay->planLabel() }}, {{ $__pay->amount }} ₽</li>
            @endforeach
        </ul>
    @endif
</div>

<style>
    .premium-status { padding: 14px 18px; border-radius: 12px; background: #f6f8fc; border: 1px solid #e0e4f1; }
    .premium-status--active { background: #e8fbfa; border-color: #1ccfc9; }
    .premium-benefits { list-style: none; padding: 0; margin: 8px 0 0; }
    .premium-benefits li { padding: 6px 0; }
    .premium-plans { display: flex; flex-wrap: wrap; gap: 16px; margin-top: 10px; }
    .premium-plan { position: relative; flex: 1 1 200px; max-width: 260px; padding: 22px 18px; text-align: center;
        border: 2px solid #e0e4f1; border-radius: 16px; background: #fff; }
    .premium-plan--best { border-color: #1ccfc9; }
    .premium-plan__badge { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); white-space: nowrap;
        background: #1ccfc9; color: #fff; font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 10px; }
    .premium-plan__title { font-weight: 600; }
    .premium-plan__price { font-size: 30px; font-weight: 700; margin: 6px 0 2px; }
    .premium-plan__hint { color: #777; font-size: 13px; margin-bottom: 14px; }
    .premium-plan button:disabled { opacity: .5; cursor: not-allowed; }
    .premium-payments { padding-left: 18px; color: #555; }
</style>

<script>
    // Данные о подписке для JS (модалка питомца и т.п.)
    window.userPremium = {{ $__isPremium ? 'true' : 'false' }};
    window.petPhotoLimit = {{ (int) $user->petPhotoLimit() }};
</script>
