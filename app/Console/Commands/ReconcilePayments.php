<?php

namespace App\Console\Commands;

use App\Models\BoostPayment;
use App\Models\PremiumPayment;
use App\Services\Payments\YooKassaService;
use Illuminate\Console\Command;

/**
 * Страховка на случай, если вебхук ЮKassa не дошёл, а пользователь не вернулся на сайт:
 * проверяет «зависшие» (pending) платежи через API ЮKassa и активирует оплаченные.
 */
class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile {--days=7 : за сколько последних дней проверять платежи}';

    protected $description = 'Сверить платежи в статусе pending с ЮKassa и активировать оплаченные';

    public function handle(YooKassaService $yookassa): int
    {
        if (! $yookassa->configured()) {
            $this->warn('ЮKassa не настроена (YOOKASSA_SHOP_ID / YOOKASSA_SECRET_KEY).');

            return self::SUCCESS;
        }

        $since  = now()->subDays((int) $this->option('days'));
        $before = now()->subMinutes(2); // свежие платежи ещё может обрабатывать пользователь/вебхук

        $groups = [
            'premium' => [PremiumPayment::class, fn ($p) => $yookassa->settlePremium($p)],
            'boost'   => [BoostPayment::class, fn ($p) => $yookassa->settleBoost($p)],
        ];

        foreach ($groups as $name => [$model, $settle]) {
            $query = $model::where('gateway', 'yookassa')
                ->where('status', 'pending')
                ->where('created_at', '>=', $since)
                ->where('created_at', '<=', $before);

            $orphans = (clone $query)->whereNull('external_id')->count();

            if ($orphans) {
                $this->warn("{$name}: {$orphans} платеж(ей) без external_id — платёж в ЮKassa не создавался или не сохранился, сверить нельзя.");
            }

            $query->whereNotNull('external_id')->orderBy('id')->each(function ($payment) use ($name, $settle) {
                try {
                    $this->line("{$name} #{$payment->id}: " . $settle($payment));
                } catch (\Throwable $e) {
                    report($e);
                    $this->error("{$name} #{$payment->id}: ошибка — " . $e->getMessage());
                }
            });
        }

        return self::SUCCESS;
    }
}
