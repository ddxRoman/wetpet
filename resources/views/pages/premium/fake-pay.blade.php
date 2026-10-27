@extends('layouts.catalog')

@section('content')
<div class="container mt-5 mb-5" style="max-width: 520px;">
    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
        <div class="card-body p-4 text-center">
            <h1 class="h4 fw-bold mb-3">Тестовая оплата</h1>
            <p class="text-muted">
                Это заглушка платёжной системы для локальной разработки. Деньги не списываются.
            </p>
            <p class="mb-4">
                Тариф: <strong>{{ $payment->planLabel() }}</strong><br>
                Сумма: <strong>{{ $payment->amount }} ₽</strong>
            </p>

            <form method="POST" action="{{ route('premium.fake.confirm', $payment) }}">
                @csrf
                <button type="submit" class="btn btn-primary px-4" style="border-radius: 12px;">Оформить подписку (тест)</button>
                <a href="{{ route('account') }}#premium" class="btn btn-light px-4" style="border-radius: 12px;">Отмена</a>
            </form>
        </div>
    </div>
</div>
@endsection
