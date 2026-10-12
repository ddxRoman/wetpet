@extends('layouts.app')

<title>Продвижение — {{ $entity->name ?? '' }} — Зверозор</title>

@section('content')
@include('layouts.header')

@php
    $periods = config('boost.periods');
@endphp

<div class="container my-4" style="max-width: 1100px;">

    <div class="mb-3">
        <a href="{{ route('owner.' . $type, $entity->id) }}" class="text-decoration-none text-muted" style="font-size:14px;">← Вернуться в кабинет</a>
    </div>

    <h1 class="fs-3 fw-bold mb-1">Продвижение</h1>
    <div class="text-muted mb-4">{{ $entity->name }}</div>

    @if(session('boost_success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            ✅ {{ session('boost_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('boost_error'))
        <div class="alert alert-danger rounded-3">{{ session('boost_error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger rounded-3">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    @if($gatewayName === 'fake')
        <div class="alert alert-warning rounded-3">
            🧪 Тестовый режим оплаты: деньги не списываются, пакет включится после нажатия «Оплатить (тест)».
        </div>
    @elseif(! $gatewayReady)
        <div class="alert alert-info rounded-3">Оплата пока недоступна — мы подключаем платёжную систему.</div>
    @endif

    <div class="row g-4">
        @foreach($plans as $key => $plan)
            @php
                $sub      = $subscriptions->get($key);
                $isActive = $sub && $sub->isActive();
                $saving   = $plan['month'] * 12 - $plan['year'];
            @endphp
            <div class="col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-3 {{ $isActive ? 'border border-2 border-success' : '' }}">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                            <h2 class="fs-5 fw-bold mb-0">{{ $plan['title'] }}</h2>
                            @if($isActive)
                                <span class="badge bg-success flex-shrink-0">Активен</span>
                            @endif
                        </div>

                        @if($isActive)
                            <div class="text-success small mb-2">Действует до {{ $sub->expires_at->format('d.m.Y') }}</div>
                        @elseif($sub && $sub->expires_at)
                            <div class="text-muted small mb-2">Закончился {{ $sub->expires_at->format('d.m.Y') }}</div>
                        @endif

                        <ul class="list-unstyled mb-4" style="font-size:14px;">
                            @foreach($plan['features'] as $feature)
                                <li class="d-flex gap-2 mb-2"><span class="text-success">✓</span><span>{{ $feature }}</span></li>
                            @endforeach
                        </ul>

                        <div class="mt-auto">
                            <div class="mb-3">
                                <span class="fs-3 fw-bold">{{ number_format($plan['month'], 0, ',', ' ') }} ₽</span>
                                <span class="text-muted">/ месяц</span>
                                <div class="text-muted small">
                                    или {{ number_format($plan['year'], 0, ',', ' ') }} ₽ / год
                                    <span class="text-success">(выгоднее на {{ number_format($saving, 0, ',', ' ') }} ₽)</span>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('owner.boost.checkout', ['type' => $type, 'id' => $entity->id]) }}" class="d-grid gap-2">
                                @csrf
                                <input type="hidden" name="package" value="{{ $key }}">
                                <button type="submit" name="period" value="month" class="btn btn-primary rounded-pill" @disabled(! $gatewayReady)>
                                    {{ $isActive ? 'Продлить' : 'Подключить' }} на месяц — {{ number_format($plan['month'], 0, ',', ' ') }} ₽
                                </button>
                                <button type="submit" name="period" value="year" class="btn btn-outline-primary rounded-pill" @disabled(! $gatewayReady)>
                                    {{ $isActive ? 'Продлить' : 'Подключить' }} на год — {{ number_format($plan['year'], 0, ',', ' ') }} ₽
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($subscriptions->contains(fn ($s) => $s->isActive()))
        <p class="text-muted small mt-3">При продлении новый срок добавляется к текущему.</p>
    @endif

    @if($payments->isNotEmpty())
        <h2 class="fs-5 fw-semibold mt-5 mb-3">Последние платежи</h2>
        <ul class="list-group shadow-sm">
            @foreach($payments as $pay)
                <li class="list-group-item d-flex justify-content-between flex-wrap gap-2">
                    <span>{{ optional($pay->paid_at)->format('d.m.Y') }} — {{ $pay->packageLabel() }}, {{ $pay->periodLabel() }}</span>
                    <span class="fw-semibold">{{ number_format($pay->amount, 0, ',', ' ') }} ₽</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>

@include('layouts.footer')
@endsection
