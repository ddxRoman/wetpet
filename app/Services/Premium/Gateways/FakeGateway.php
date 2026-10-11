<?php

namespace App\Services\Premium\Gateways;

use App\Models\PremiumPayment;

/**
 * Тестовый шлюз для локальной разработки: ведёт на внутреннюю страницу
 * с кнопкой «Оплатить», деньги не списываются.
 */
class FakeGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'fake';
    }

    public function createPayment(PremiumPayment $payment): string
    {
        return route('premium.fake.show', $payment);
    }
}
