<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Organization;
use App\Models\Specialist;
use Illuminate\Console\Command;

/**
 * Чинит уже сохранённые в БД значения seo_title / seo_description / description,
 * в которые вместо названия города попал целиком JSON модели City.
 *
 * Причина бага: у Doctor/Specialist "city" — это связь (метод city()), а не строка.
 * Где-то раньше (не в текущем коде — see App\Services\SeoManager) название города
 * подставлялось в текст как "{$model->city}" вместо "{$model->city->name}", и вместо
 * названия города в строку попадал JSON всей модели City (Model::__toString()).
 * Этот баг уже исправлен в App\Services\SeoManager, но старые тексты, сохранённые
 * ДО исправления, остаются испорченными в БД — их чинит эта команда.
 */
class FixJsonInSeoFields extends Command
{
    protected $signature = 'app:fix-json-in-seo {--dry-run : Показать, что будет исправлено, но не сохранять}';

    protected $description = 'Заменяет случайно вставленный JSON модели City на название города в seo_title/seo_description/description';

    // Ищем фрагмент вида {"id":31,...,"updated_at":"..."} — сериализованная модель City
    private const JSON_PATTERN = '/\{"id":\d+.*?"updated_at":"[^"]*"\}/u';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $fixedTotal = 0;

        foreach ([Doctor::class, Specialist::class, Organization::class, Clinic::class] as $modelClass) {
            $rows = $modelClass::query()
                ->where(function ($q) {
                    $q->where('seo_title', 'like', '%"id":%')
                      ->orWhere('seo_description', 'like', '%"id":%')
                      ->orWhere('description', 'like', '%"id":%');
                })
                ->get();

            foreach ($rows as $row) {
                $changed = false;

                foreach (['seo_title', 'seo_description', 'description'] as $field) {
                    $value = $row->{$field};
                    if (!$value || !str_contains($value, '"id":')) {
                        continue;
                    }

                    $fixed = preg_replace_callback(self::JSON_PATTERN, function ($m) {
                        $decoded = json_decode($m[0], true);
                        return $decoded['name'] ?? '';
                    }, $value);

                    if ($fixed !== $value) {
                        $this->line(sprintf(
                            '[%s #%d] %s: "%s…" → "%s…"',
                            class_basename($row),
                            $row->id,
                            $field,
                            mb_substr($value, 0, 60),
                            mb_substr($fixed, 0, 60)
                        ));
                        $row->{$field} = $fixed;
                        $changed = true;
                    }
                }

                if ($changed) {
                    $fixedTotal++;
                    if (!$dryRun) {
                        // saveQuietly — чтобы не задеть boot()-хуки модели (например,
                        // пересчёт slug у Doctor при isDirty), мы просто чиним текст.
                        $row->saveQuietly();
                    }
                }
            }
        }

        if ($fixedTotal === 0) {
            $this->info('Испорченных записей не найдено.');
            return self::SUCCESS;
        }

        $this->info($dryRun
            ? "Найдено записей для исправления: {$fixedTotal} (ничего не сохранено — это --dry-run)"
            : "Исправлено записей: {$fixedTotal}");

        return self::SUCCESS;
    }
}
