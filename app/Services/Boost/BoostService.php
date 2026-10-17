<?php

namespace App\Services\Boost;

use App\Models\BoostPayment;
use App\Models\BoostSubscription;
use App\Models\User;
use App\Services\Boost\Gateways\BoostGateway;
use App\Services\Boost\Gateways\FakeBoostGateway;
use Illuminate\Support\Facades\DB;

class BoostService
{
    /** Врачи и специалисты покупают пакеты со своим набором (config('boost.specialist_plans')). */
    public static function isPerson(?string $type): bool
    {
        return in_array($type, ['doctor', 'specialist'], true);
    }

    /** Пакеты для типа карточки (без типа — пакеты клиник и организаций). */
    public function plans(?string $type = null): array
    {
        return self::isPerson($type)
            ? config('boost.specialist_plans', [])
            : config('boost.plans', []);
    }

    /**
     * Активный шлюз или null, если оплата не настроена.
     * Выбор шлюза общий с Премиумом (config('premium.gateway')).
     * Новую платёжную систему подключаем здесь.
     */
    public function gateway(): ?BoostGateway
    {
        return match (config('premium.gateway')) {
            'fake'  => app(FakeBoostGateway::class),
            default => null,
        };
    }

    /** Создаёт платёж в статусе pending (сумма берётся из конфига, а не от клиента). */
    public function createPayment(User $user, string $type, int $id, string $package, string $period, string $gatewayName): BoostPayment
    {
        $amount = $this->plans($type)[$package][$period] ?? null;

        abort_if($amount === null || ! isset(config('boost.periods')[$period]), 422, 'Неизвестный пакет или период.');

        return BoostPayment::create([
            'user_id'     => $user->id,
            'entity_type' => $type,
            'entity_id'   => $id,
            'package'     => $package,
            'period'      => $period,
            'amount'      => $amount,
            'currency'    => 'RUB',
            'status'      => 'pending',
            'gateway'     => $gatewayName,
        ]);
    }

    /**
     * Подтверждает оплаченный платёж и продлевает пакет карточки.
     * Идемпотентно: повторный вызов для уже оплаченного платежа ничего не делает.
     * Если пакет ещё активен — новый срок прибавляется к текущему окончанию.
     */
    public function activate(BoostPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $locked = BoostPayment::whereKey($payment->id)->lockForUpdate()->first();

            if (! $locked || $locked->status === 'paid') {
                return;
            }

            $months = (int) config("boost.periods.{$locked->period}.months", 0);

            if ($months < 1) {
                return;
            }

            $subscription = BoostSubscription::where('entity_type', $locked->entity_type)
                ->where('entity_id', $locked->entity_id)
                ->where('package', $locked->package)
                ->lockForUpdate()
                ->first();

            if (! $subscription) {
                $subscription = new BoostSubscription([
                    'entity_type' => $locked->entity_type,
                    'entity_id'   => $locked->entity_id,
                    'package'     => $locked->package,
                ]);
            }

            $base = $subscription->isActive() ? $subscription->expires_at->copy() : now();

            $subscription->expires_at = $base->addMonthsNoOverflow($months);
            $subscription->save();

            $locked->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);
        });
    }
}
