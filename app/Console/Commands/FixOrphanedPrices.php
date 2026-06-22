<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Диагностика и очистка "осиротевших" записей в таблице prices —
 * тех, у которых service_id указывает на несуществующую услугу.
 *
 * Возникло из-за старого ServiceSeeder, который делал
 * DB::table('services')->truncate() при каждом запуске — id услуг
 * пересоздавались с нуля, и старые ссылки в prices.service_id
 * указывали "в пустоту" либо на чужую услугу.
 *
 * Использование:
 *   php artisan prices:fix-orphaned          — только показать отчёт
 *   php artisan prices:fix-orphaned --delete — удалить осиротевшие записи
 */
class FixOrphanedPrices extends Command
{
    protected $signature = 'prices:fix-orphaned {--delete : Удалить найденные осиротевшие записи}';

    protected $description = 'Найти (и опционально удалить) цены, ссылающиеся на несуществующие услуги';

    public function handle(): int
    {
        $orphaned = DB::table('prices')
            ->leftJoin('services', 'services.id', '=', 'prices.service_id')
            ->whereNull('services.id')
            ->select('prices.*')
            ->get();

        if ($orphaned->isEmpty()) {
            $this->info('✅ Осиротевших записей не найдено. Всё чисто.');
            return self::SUCCESS;
        }

        $this->warn("Найдено {$orphaned->count()} осиротевших записей в prices:");
        $this->table(
            ['id', 'priceable_type', 'priceable_id', 'service_id (битый)', 'price'],
            $orphaned->map(fn($p) => [
                $p->id, $p->priceable_type, $p->priceable_id, $p->service_id, $p->price,
            ])
        );

        if ($this->option('delete')) {
            $ids = $orphaned->pluck('id');
            DB::table('prices')->whereIn('id', $ids)->delete();
            $this->info("🗑  Удалено {$ids->count()} осиротевших записей.");
        } else {
            $this->comment('Запустите команду с флагом --delete чтобы удалить эти записи.');
        }

        return self::SUCCESS;
    }
}
