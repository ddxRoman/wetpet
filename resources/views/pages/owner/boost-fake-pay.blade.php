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
                Пакет: <strong>{{ $payment->packageLabel() }}</strong><br>
                Период: <strong>{{ $payment->periodLabel() }}</strong><br>
                Сумма: <strong>{{ number_format($payment->amount, 0, ',', ' ') }} ₽</strong>
            </p>

            <form method="POST" action="{{ route('owner.boost.fake.confirm', $payment) }}">
                @csrf
                <button type="submit" class="btn btn-primary px-4" style="border-radius: 12px;">Оформить подписку (тест)</button>
                <a href="{{ route('owner.boost', ['type' => $payment->entity_type, 'id' => $payment->entity_id]) }}"
                   class="btn btn-light px-4" style="border-radius: 12px;">Отмена</a>
            </form>
        </div>
    </div>
</div>
@endsection
