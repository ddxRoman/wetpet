<?php

namespace App\Console\Commands;

use App\Services\Payments\AutoRenewalService;
use Illuminate\Console\Command;

class RenewSubscriptions extends Command
{
    protected $signature = 'subscriptions:renew';

    protected $description = 'Автопродление Премиума и пакетов продвижения (списание по сохранённому способу оплаты)';

    public function handle(AutoRenewalService $renewals): int
    {
        $count = $renewals->runDue();

        $this->info("Попыток списания: {$count}");

        return self::SUCCESS;
    }
}
