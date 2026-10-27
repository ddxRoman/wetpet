<?php

namespace App\Services\Payments;

use App\Models\AutoRenewal;
use App\Models\BoostPayment;
use App\Models\BoostSubscription;
use App\Models\PremiumPayment;
use App\Models\User;
use App\Notifications\AutoRenewalFailed;
use App\Services\Boost\BoostService;
use App\Services\Premium\PremiumService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use YooKassa\Common\Exceptions\BadApiRequestException;
use YooKassa\Common\Exceptions\NotFoundException;

/**
 * Автопродление Премиума и пакетов продвижения через автоплатежи ЮKassa.
 *
 * Жизненный цикл:
 *  1. Пользователь ставит галочку при оплате → платёж создаётся с save_payment_method.
 *  2. После успешной оплаты onPaid() сохраняет id способа оплаты (AutoRenewal).
 *  3. Команда subscriptions:renew раз в час вызывает runDue(): за сутки до окончания
 *     создаётся платёж по сохранённому способу оплаты.
 *  4. Отказ банка → до max_attempts повторов, затем автопродление отключается, пользователю уходит письмо.
 */
class AutoRenewalService
{
    public function __construct(private YooKassaService $yookassa)
    {
    }

    /* ---------- Запись способа оплаты ---------- */

    public function scopeFor(Model $payment): ?string
    {
        return match (true) {
            $payment instanceof PremiumPayment => AutoRenewal::premiumKey((int) $payment->user_id),
            $payment instanceof BoostPayment   => AutoRenewal::boostKey($payment->entity_type, (int) $payment->entity_id, $payment->package),
            default                            => null,
        };
    }

    /**
     * Вызывается после успешной оплаты (из YooKassaService::settle).
     * Идемпотентно: можно вызывать повторно.
     */
    public function onPaid(Model $payment, $remote): void
    {
        $scope = $this->scopeFor($payment);

        if (! $scope) {
            return;
        }

        // Успешное автосписание — сбрасываем счётчик неудач
        if ($payment->is_renewal) {
            AutoRenewal::where('scope_key', $scope)->update(['attempts' => 0]);

            return;
        }

        if (! $payment->auto_renew) {
            return;
        }

        $method = $remote->getPaymentMethod();

        // Согласие есть, но способ оплаты не сохранился (например, оплата не тем способом) — просто не включаем
        if (! $method || ! $method->getSaved() || ! $method->getId()) {
            return;
        }

        $common = [
            'user_id'              => $payment->user_id,
            'payment_method_id'    => $method->getId(),
            'payment_method_title' => $method->getTitle(),
            'status'               => 'active',
            'attempts'             => 0,
            'last_attempt_at'      => null,
            'canceled_at'          => null,
        ];

        $specific = $payment instanceof PremiumPayment
            ? ['kind' => 'premium', 'plan' => $payment->plan]
            : [
                'kind'        => 'boost',
                'entity_type' => $payment->entity_type,
                'entity_id'   => $payment->entity_id,
                'package'     => $payment->package,
                'plan'        => $payment->period,
            ];

        AutoRenewal::updateOrCreate(['scope_key' => $scope], $common + $specific);
    }

    /** Отключение автопродления пользователем. Сохранённый способ оплаты у нас стираем. */
    public function cancel(AutoRenewal $renewal, string $status = 'canceled'): void
    {
        $renewal->update([
            'status'            => $status,
            'payment_method_id' => null,
            'canceled_at'       => now(),
        ]);
    }

    /* ---------- Списание ---------- */

    public function runDue(): int
    {
        $processed = 0;

        AutoRenewal::where('status', 'active')->orderBy('id')->chunkById(100, function ($items) use (&$processed) {
            foreach ($items as $renewal) {
                try {
                    if ($this->process($renewal)) {
                        $processed++;
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        });

        return $processed;
    }

    /** Окончание текущей подписки; null — подписки нет. */
    public function expiresAt(AutoRenewal $renewal): ?Carbon
    {
        if ($renewal->kind === 'premium') {
            $until = User::whereKey($renewal->user_id)->value('premium_until');

            return $until ? Carbon::parse($until) : null;
        }

        $expires = BoostSubscription::where('entity_type', $renewal->entity_type)
            ->where('entity_id', $renewal->entity_id)
            ->where('package', $renewal->package)
            ->value('expires_at');

        return $expires ? Carbon::parse($expires) : null;
    }

    /** @return bool была ли предпринята попытка списания */
    public function process(AutoRenewal $renewal): bool
    {
        $cfg = config('premium.auto_renew');

        // Занимаем попытку под блокировкой, чтобы два запуска не списали деньги дважды
        $claimed = DB::transaction(function () use ($renewal, $cfg) {
            $r = AutoRenewal::whereKey($renewal->id)->lockForUpdate()->first();

            if (! $r || ! $r->isActive()) {
                return null;
            }

            $expires = $this->expiresAt($r);

            if (! $expires) {
                $this->cancel($r);

                return null;
            }

            if ($expires->gt(now()->addHours((int) $cfg['charge_before_hours']))) {
                return null; // ещё рано
            }

            if ($r->last_attempt_at && $r->last_attempt_at->gt(now()->subHours((int) $cfg['retry_after_hours']))) {
                return null; // недавно уже пробовали
            }

            $r->attempts        = $r->attempts + 1;
            $r->last_attempt_at = now();
            $r->save();

            return [$r, $expires];
        });

        if (! $claimed) {
            return false;
        }

        [$r, $expires] = $claimed;

        $payment = $this->localRenewalPayment($r);

        if (! $payment) {
            // Тариф удалён из конфига — списывать нечего
            $this->cancel($r);

            return false;
        }

        // Ключ идемпотентности: повтор той же попытки (после сбоя сети) не создаст второй платёж в ЮKassa
        $key = sprintf('renew-%d-%s-%d', $r->id, $expires->format('Ymd'), $r->attempts);

        try {
            $remote = $this->yookassa->createRecurringPayment(
                $payment->amount,
                $this->description($r),
                $r->payment_method_id,
                $key,
                $this->metadata($r, $payment)
            );
        } catch (BadApiRequestException|NotFoundException $e) {
            // ЮKassa однозначно отклонила запрос (например, способ оплаты больше недоступен)
            report($e);
            $payment->update(['status' => 'canceled']);
            $this->handleFailure($r, 'rejected');

            return true;
        } catch (\Throwable $e) {
            // Неясно, создан ли платёж (сеть, 5xx, неверные ключи). Не считаем это отказом пользователя:
            // возвращаем попытку, платёж останется pending без external_id и будет использован повторно с тем же ключом.
            report($e);
            AutoRenewal::whereKey($r->id)->update([
                'attempts'        => max(0, $r->attempts - 1),
                'last_attempt_at' => null,
            ]);

            return true;
        }

        $payment->update(['external_id' => $remote->getId()]);

        $status = $payment instanceof PremiumPayment
            ? $this->yookassa->settlePremium($payment)
            : $this->yookassa->settleBoost($payment);

        if ($status === 'canceled') {
            $details = $remote->getCancellationDetails();
            $this->handleFailure($r, $details ? $details->getReason() : null);
        }

        // paid — подписка продлена; pending — дождёмся вебхука (автоплатёж без 3-D Secure обычно финален сразу)
        return true;
    }

    /* ---------- Вспомогательное ---------- */

    private function handleFailure(AutoRenewal $r, ?string $reason): void
    {
        $r->refresh();

        $revoked  = $reason === 'permission_revoked';
        $disabled = $revoked || $r->attempts >= (int) config('premium.auto_renew.max_attempts');

        if ($disabled) {
            $this->cancel($r, 'failed');
        }

        try {
            $r->user?->notify(new AutoRenewalFailed($r->title(), $disabled, $reason));
        } catch (\Throwable $e) {
            report($e); // почта не настроена или недоступна — не мешаем остальным списаниям
        }
    }

    /** Создаёт локальный pending-платёж; повторно использует «зависший» после сбоя сети. */
    private function localRenewalPayment(AutoRenewal $r): ?Model
    {
        if ($r->kind === 'premium') {
            if (! isset(config('premium.plans')[$r->plan])) {
                return null;
            }

            $payment = PremiumPayment::where('user_id', $r->user_id)
                ->where('is_renewal', true)->where('status', 'pending')->whereNull('external_id')
                ->where('created_at', '>', now()->subDay())->latest('id')->first();

            if (! $payment) {
                $payment = app(PremiumService::class)->createPayment($r->user, $r->plan, 'yookassa');
                $payment->update(['is_renewal' => true]);
            }

            return $payment;
        }

        if (! isset(app(BoostService::class)->plans($r->entity_type)[$r->package][$r->plan])) {
            return null;
        }

        $payment = BoostPayment::where('user_id', $r->user_id)
            ->where('entity_type', $r->entity_type)->where('entity_id', $r->entity_id)
            ->where('package', $r->package)->where('period', $r->plan)
            ->where('is_renewal', true)->where('status', 'pending')->whereNull('external_id')
            ->where('created_at', '>', now()->subDay())->latest('id')->first();

        if (! $payment) {
            $payment = app(BoostService::class)->createPayment(
                $r->user, $r->entity_type, (int) $r->entity_id, $r->package, $r->plan, 'yookassa'
            );
            $payment->update(['is_renewal' => true]);
        }

        return $payment;
    }

    private function description(AutoRenewal $r): string
    {
        return 'Зверозор: автопродление, ' . $r->title() . ', ' . $r->planLabel();
    }

    private function metadata(AutoRenewal $r, Model $payment): array
    {
        return array_filter([
            'kind'        => $r->kind,
            'payment_id'  => $payment->id,
            'user_id'     => $r->user_id,
            'entity_type' => $r->entity_type,
            'entity_id'   => $r->entity_id,
            'renewal'     => 'auto',
        ], fn ($v) => $v !== null);
    }
}
