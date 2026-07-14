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
            // Генерируем slug вручную той же логикой что в модели
            $translitMap = [
                'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo',
                'ж'=>'zh','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m',
                'н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u',
                'ф'=>'f','х'=>'kh','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'shch',
                'ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
                'А'=>'a','Б'=>'b','В'=>'v','Г'=>'g','Д'=>'d','Е'=>'e','Ё'=>'yo',
                'Ж'=>'zh','З'=>'z','И'=>'i','Й'=>'y','К'=>'k','Л'=>'l','М'=>'m',
                'Н'=>'n','О'=>'o','П'=>'p','Р'=>'r','С'=>'s','Т'=>'t','У'=>'u',
                'Ф'=>'f','Х'=>'kh','Ц'=>'ts','Ч'=>'ch','Ш'=>'sh','Щ'=>'shch',
                'Ъ'=>'','Ы'=>'y','Ь'=>'','Э'=>'e','Ю'=>'yu','Я'=>'ya',
            ];

            $source = collect([$org->name, $org->city, $org->street, $org->house])
                ->filter()->implode('-');
            $translit = strtr($source, $translitMap);
            $base = \Illuminate\Support\Str::slug($translit) ?: 'org-' . $org->id;

            $slug = $base;
            $count = 1;
            while (Organization::where('slug', $slug)->where('id', '!=', $org->id)->exists()) {
                $slug = "{$base}-{$count}";
                $count++;
            }

            $org->slug = $slug;
            $org->saveQuietly();
            $this->line("  ✓ [{$org->id}] {$org->name} → {$org->slug}");
        }

        $this->info('Готово!');
    }
}