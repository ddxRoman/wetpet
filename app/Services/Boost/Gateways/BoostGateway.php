<?php

namespace App\Services\Boost\Gateways;

use App\Models\BoostPayment;

interface BoostGateway
{
    /** Короткое имя шлюза — пишется в boost_payments.gateway */
    public function name(): string;

    /**
     * Регистрирует платёж в платёжной системе и возвращает URL,
     * на который нужно отправить пользователя для оплаты.
     */
    public function createPayment(BoostPayment $payment): string;
}
