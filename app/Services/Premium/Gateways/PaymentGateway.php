<?php

namespace App\Services\Premium\Gateways;

use App\Models\PremiumPayment;

interface PaymentGateway
{
    /** Короткое имя шлюза — пишется в premium_payments.gateway */
    public function name(): string;

    /**
     * Регистрирует платёж в платёжной системе и возвращает URL,
     * на который нужно отправить пользователя для оплаты.
     */
    public function createPayment(PremiumPayment $payment): string;
}
