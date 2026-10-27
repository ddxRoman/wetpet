<?php

namespace App\Services\Premium\Gateways;

use App\Models\PremiumPayment;
use App\Services\Payments\YooKassaService;

/**
 * Оплата Премиум-подписки через ЮKassa (редирект на страницу оплаты).
 */
class YooKassaGateway implements PaymentGateway
{
    public function __construct(private YooKassaService $yookassa)
    {
    }

    public function name(): string
    {
        return 'yookassa';
    }

    public function createPayment(PremiumPayment $payment): string
    {
        $result = $this->yookassa->createRedirectPayment(
            $payment->amount,
            'Зверозор: Премиум-подписка, ' . $payment->planLabel(),
            route('premium.yookassa.return', $payment),
            uniqid('premium-' . $payment->id . '-', true),
            [
                'kind'       => 'premium',
                'payment_id' => $payment->id,
                'user_id'    => $payment->user_id,
            ],
            (bool) $payment->auto_renew
        );

        $payment->update(['external_id' => $result['id']]);

        return $result['url'];
    }
}
