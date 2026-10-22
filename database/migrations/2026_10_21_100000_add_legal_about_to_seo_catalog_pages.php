<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Идемпотентно: если запись уже создана вручную — не трогаем
        if (DB::table('seo_catalog_pages')->where('key', 'legal_about')->exists()) {
            return;
        }

        $now = now();

        DB::table('seo_catalog_pages')->insert([
            'key'         => 'legal_about',
            'label'       => 'О проекте (/legal/about)',
            'title'       => 'О нас — Зверозор',
            'description' => 'Информация о миссии проекта Зверозор, ветеринарных клиниках, груминг-салонах и поиске специалистов для ваших питомцев.',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('seo_catalog_pages')->where('key', 'legal_about')->delete();
    }
};
