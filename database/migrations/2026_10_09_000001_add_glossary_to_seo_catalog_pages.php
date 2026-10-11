<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('seo_catalog_pages')->insert([
            'key'         => 'glossary',
            'label'        => 'Словарь терминов (/legal/glossary)',
            'title'       => 'Словарь ветеринарных терминов — определения и значения | Зверозор',
            'description' => 'Словарь ветеринарных и юридических терминов на Зверозор: простые определения, анатомия, болезни, процедуры и термины платформы.',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('seo_catalog_pages')->where('key', 'glossary')->delete();
    }
};
