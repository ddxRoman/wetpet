<?php

namespace App\Services\Boost\Gateways;

use App\Models\BoostPayment;

/**
 * Тестовый шлюз: ведёт на внутреннюю страницу с кнопкой «Оплатить (тест)», деньги не списываются.
 */
class FakeBoostGateway implements BoostGateway
{
    public function name(): string
    {
        return 'fake';
    }

    public function createPayment(BoostPayment $payment): string
    {
        return route('owner.boost.fake.show', $payment);
    }
}
