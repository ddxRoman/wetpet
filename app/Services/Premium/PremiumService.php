<?php

namespace App\Services\Premium;

use App\Models\PremiumPayment;
use App\Models\User;
use App\Services\Premium\Gateways\FakeGateway;
use App\Services\Premium\Gateways\PaymentGateway;
use Illuminate\Support\Facades\DB;

class PremiumService
{
    public function plans(): array
    {
        return config('premium.plans', []);
    }

    /**
     * Активный шлюз или null, если оплата не настроена.
     * Новую платёжную систему подключаем здесь (match по config('premium.gateway')).
     */
    public function gateway(): ?PaymentGateway
    {
        return match (config('premium.gateway')) {
            'fake'  => app(FakeGateway::class),
            default => null,
        };
    }

    /** Создаёт запись о платеже в статусе pending (сумма берётся из конфига, а не от клиента). */
    public function createPayment(User $user, string $plan, string $gatewayName): PremiumPayment
    {
        $planConfig = $this->plans()[$plan] ?? null;

        abort_if($planConfig === null, 422, 'Неизвестный тариф.');

        return PremiumPayment::create([
            'user_id'  => $user->id,
            'plan'     => $plan,
            'amount'   => $planConfig['price'],
            'currency' => 'RUB',
            'status'   => 'pending',
            'gateway'  => $gatewayName,
        ]);
    }

    /**
     * Подтверждает оплаченный платёж и продлевает подписку.
     * Идемпотентно: повторный вызов для уже оплаченного платежа ничего не делает
     * (платёжные системы могут прислать уведомление несколько раз).
     * Если подписка ещё активна — новый срок прибавляется к текущему окончанию.
     */
    public function activate(PremiumPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $locked = PremiumPayment::whereKey($payment->id)->lockForUpdate()->first();

            if (! $locked || $locked->status === 'paid') {
                return;
            }

            $user = User::whereKey($locked->user_id)->lockForUpdate()->first();
            $months = (int) config("premium.plans.{$locked->plan}.months", 0);

            if (! $user || $months < 1) {
                return;
            }

            $base = $user->isPremium() ? $user->premium_until->copy() : now();

            $user->premium_until = $base->addMonthsNoOverflow($months);
            $user->save();

            $locked->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);
        });
    }
}
