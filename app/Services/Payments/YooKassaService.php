<?php

namespace App\Services\Payments;

use App\Models\BoostPayment;
use App\Models\PremiumPayment;
use App\Services\Boost\BoostService;
use App\Services\Premium\PremiumService;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use YooKassa\Client;

/**
 * Общая обёртка над SDK ЮKassa для Премиума и Продвижения.
 * Ключи берутся из .env: YOOKASSA_SHOP_ID / YOOKASSA_SECRET_KEY (см. config/services.php).
 */
class YooKassaService
{
    public function configured(): bool
    {
        return filled(config('services.yookassa.shop_id'))
            && filled(config('services.yookassa.secret_key'));
    }

    /** Разрешены ли автоплатежи (подписка с автопродлением). См. YOOKASSA_RECURRING в .env. */
    public function recurringEnabled(): bool
    {
        return $this->configured() && (bool) config('services.yookassa.recurring');
    }

    public function client(): Client
    {
        if (! $this->configured()) {
            throw new RuntimeException('ЮKassa: не заданы YOOKASSA_SHOP_ID / YOOKASSA_SECRET_KEY.');
        }

        $client = new Client();
        $client->setAuth(
            config('services.yookassa.shop_id'),
            config('services.yookassa.secret_key')
        );

        return $client;
    }

    /**
     * Создаёт платёж с редиректом на страницу оплаты ЮKassa.
     *
     * @return array{id: string, url: string}
     */
    public function createRedirectPayment(
        int|float|string $amount,
        string $description,
        string $returnUrl,
        string $idempotenceKey,
        array $metadata = [],
        bool $savePaymentMethod = false
    ): array {
        $payload = [
            'amount' => $this->amount($amount),
            'confirmation' => [
                'type'       => 'redirect',
                'return_url' => $returnUrl,
            ],
            'capture'     => true,
            // ЮKassa ограничивает описание 128 символами
            'description' => Str::limit($description, 128, ''),
        ];

        if ($metadata) {
            $payload['metadata'] = $metadata;
        }

        // Пользователь дал согласие на автопродление: ЮKassa привяжет способ оплаты к магазину
        if ($savePaymentMethod) {
            $payload['save_payment_method'] = true;
        }

        $payment      = $this->client()->createPayment($payload, $idempotenceKey);
        $confirmation = $payment->getConfirmation();
        $url          = $confirmation ? $confirmation->getConfirmationUrl() : null;

        if (! $url) {
            throw new RuntimeException('ЮKassa не вернула ссылку на оплату (платёж ' . $payment->getId() . ').');
        }

        return ['id' => $payment->getId(), 'url' => $url];
    }

    private function amount(int|float|string $amount): array
    {
        return [
            'value'    => number_format((float) $amount, 2, '.', ''),
            'currency' => 'RUB',
        ];
    }

    /**
     * Автоплатёж по сохранённому способу оплаты (без участия пользователя).
     * Возвращает объект платежа ЮKassa; итоговый статус смотрим через settle*().
     *
     * @return \YooKassa\Request\Payments\CreatePaymentResponse
     */
    public function createRecurringPayment(
        int|float|string $amount,
        string $description,
        string $paymentMethodId,
        string $idempotenceKey,
        array $metadata = []
    ) {
        $payload = [
            'amount'            => $this->amount($amount),
            'capture'           => true,
            'payment_method_id' => $paymentMethodId,
            'description'       => Str::limit($description, 128, ''),
        ];

        if ($metadata) {
            $payload['metadata'] = $metadata;
        }

        return $this->client()->createPayment($payload, $idempotenceKey);
    }

    /**
     * Сверяет платёж с ЮKassa и применяет результат.
     * Возвращает локальный статус: paid | canceled | pending.
     */
    public function settlePremium(PremiumPayment $payment): string
    {
        return $this->settle($payment, fn () => app(PremiumService::class)->activate($payment));
    }

    public function settleBoost(BoostPayment $payment): string
    {
        return $this->settle($payment, fn () => app(BoostService::class)->activate($payment));
    }

    /**
     * Статус всегда берём из API ЮKassa, а не из запроса пользователя или тела вебхука —
     * иначе их можно подделать. Активация идемпотентна, поэтому
     * возврат пользователя и вебхук могут безопасно прийти оба.
     */
    private function settle(Model $payment, Closure $activate): string
    {
        if ($payment->status !== 'pending' || ! $payment->external_id) {
            return (string) $payment->status;
        }

        $remote = $this->client()->getPaymentInfo($payment->external_id);
        $status = $remote->getStatus();

        if ($status === 'succeeded' && $remote->getPaid()) {
            $amount = $remote->getAmount();

            if (abs((float) $amount->getValue() - (float) $payment->amount) > 0.001
                || $amount->getCurrency() !== $payment->currency) {
                Log::error('ЮKassa: сумма платежа не совпадает с заказом', [
                    'local_id'  => $payment->getKey(),
                    'external'  => $payment->external_id,
                    'expected'  => $payment->amount . ' ' . $payment->currency,
                    'received'  => $amount->getValue() . ' ' . $amount->getCurrency(),
                ]);

                return 'pending';
            }

            $activate();

            // Сохранённый способ оплаты → автопродление (ошибка здесь не должна ломать активацию)
            try {
                app(AutoRenewalService::class)->onPaid($payment, $remote);
            } catch (\Throwable $e) {
                report($e);
            }

            return 'paid';
        }

        if ($status === 'canceled') {
            // Причина отмены нужна для диагностики (например, тестовая карта не подошла, 3-D Secure не пройден)
            $details = $remote->getCancellationDetails();

            Log::warning('ЮKassa: платёж отменён', [
                'local_id' => $payment->getKey(),
                'external' => $payment->external_id,
                'party'    => $details ? $details->getParty() : null,
                'reason'   => $details ? $details->getReason() : null,
            ]);

            $payment->newQuery()
                ->whereKey($payment->getKey())
                ->where('status', 'pending')
                ->update(['status' => 'canceled']);

            return 'canceled';
        }

        return 'pending';
    }
}
