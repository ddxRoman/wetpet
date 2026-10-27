<?php

namespace App\Services\Boost\Gateways;

use App\Models\BoostPayment;
use App\Services\Payments\YooKassaService;

/**
 * Оплата пакетов продвижения через ЮKassa (редирект на страницу оплаты).
 */
class YooKassaBoostGateway implements BoostGateway
{
    public function __construct(private YooKassaService $yookassa)
    {
    }

    public function name(): string
    {
        return 'yookassa';
    }

    public function createPayment(BoostPayment $payment): string
    {
        $result = $this->yookassa->createRedirectPayment(
            $payment->amount,
            'Зверозор: ' . $payment->packageLabel() . ', ' . $payment->periodLabel(),
            route('owner.boost.yookassa.return', $payment),
            uniqid('boost-' . $payment->id . '-', true),
            [
                'kind'        => 'boost',
                'payment_id'  => $payment->id,
                'user_id'     => $payment->user_id,
                'entity_type' => $payment->entity_type,
                'entity_id'   => $payment->entity_id,
            ],
            (bool) $payment->auto_renew
        );

        $payment->update(['external_id' => $result['id']]);

        return $result['url'];
    }
}
