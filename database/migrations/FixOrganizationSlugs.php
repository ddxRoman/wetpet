<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Organization;

class FixOrganizationSlugs extends Command
{
    protected $signature   = 'fix:organization-slugs';
    protected $description = 'Пересчитать slug для организаций с пустым или отсутствующим slug';

    public function handle(): void
    {
        $orgs = Organization::whereNull('slug')
            ->orWhere('slug', '')
            ->get();

        if ($orgs->isEmpty()) {
            $this->info('Организации с пустым slug не найдены.');
            return;
        }

        $this->info("Найдено {$orgs->count()} организаций без slug. Исправляю...");

        foreach ($orgs as $org) {
            // Вызываем generateUniqueSlug через touch() — это триггерит событие updating
            // Но проще вызвать напрямую через save() с изменённым полем
            $org->slug = Organization::generateUniqueSlug($org);
            $org->saveQuietly(); // Без запуска событий чтобы не зациклиться
            $this->line("  ✓ [{$org->id}] {$org->name} → {$org->slug}");
        }

        $this->info('Готово!');
    }
}
