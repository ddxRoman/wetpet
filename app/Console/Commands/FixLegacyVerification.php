<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Doctor;
use App\Models\Organization;
use App\Models\Clinic;
use App\Models\Specialist;
use App\Models\User;

/**
 * Одноразовая команда для исправления старых записей, созданных сидером
 * или массовым импортом ДО появления Observer'а (EntityCreationObserver).
 *
 * Такие записи обычно вставлялись через DB::table()->insert(), минуя
 * Eloquent-события — поэтому is_verified у них осталось false, хотя
 * реального "обычного пользователя с сайта", который бы их создал, нет.
 *
 * Команда помечает is_verified = true для всех записей, где:
 *   - created_by пустой (NULL), ИЛИ
 *   - created_by указывает на пользователя с is_admin = true
 *
 * Записи, реально поданные обычными пользователями через публичный сайт
 * (created_by указывает на НЕ-админа), эта команда не трогает —
 * они как и должны остаются на модерации.
 *
 * Использование:
 *   php artisan entities:fix-legacy-verification           — отчёт (dry-run)
 *   php artisan entities:fix-legacy-verification --apply    — реально применить
 */
class FixLegacyVerification extends Command
{
    protected $signature = 'entities:fix-legacy-verification {--apply : Применить изменения (без флага — только отчёт)}';

    protected $description = 'Помечает старые записи (без реального пользователя-создателя) как проверенные';

    public function handle(): int
    {
        $apply = $this->option('apply');

        $adminIds = User::where('is_admin', true)->pluck('id');

        $models = [
            'Клиники'      => Clinic::class,
            'Организации'  => Organization::class,
            'Врачи'        => Doctor::class,
            'Специалисты'  => Specialist::class,
        ];

        $this->table(['Тип', 'Найдено для исправления'], collect($models)->map(function ($class, $label) use ($adminIds) {
            $count = $class::where('is_verified', false)
                ->where(function ($q) use ($adminIds) {
                    $q->whereNull('created_by')
                      ->orWhereIn('created_by', $adminIds);
                })
                ->count();
            return [$label, $count];
        })->toArray());

        if (!$apply) {
            $this->comment('Это предварительный отчёт. Запустите команду с флагом --apply, чтобы применить изменения.');
            return self::SUCCESS;
        }

        $totalUpdated = 0;

        foreach ($models as $label => $class) {
            $updated = $class::where('is_verified', false)
                ->where(function ($q) use ($adminIds) {
                    $q->whereNull('created_by')
                      ->orWhereIn('created_by', $adminIds);
                })
                ->update(['is_verified' => true]);

            $this->info("✅ {$label}: обновлено {$updated} записей");
            $totalUpdated += $updated;
        }

        $this->info("Готово. Всего обновлено: {$totalUpdated} записей.");

        return self::SUCCESS;
    }
}
