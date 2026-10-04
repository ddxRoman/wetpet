<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('seo_catalog_pages')->insert([
            [
                'key'         => 'animals',
                'label'       => 'Животные — каталог видов (/animals)',
                'title'       => 'Животные: виды и породы с фото и описанием | Зверозор',
                'description' => 'Каталог видов и пород домашних животных на Зверозор: фото, описание, характер и уход.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'legal_faq',
                'label'       => 'Частые вопросы (/legal/faq)',
                'title'       => 'Часто задаваемые вопросы — Зверозор',
                'description' => 'Ответы на частые вопросы о сайте Зверозор: как оставить отзыв, добавить организацию, подтвердить владение карточкой и другое.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('seo_catalog_pages')->whereIn('key', ['animals', 'legal_faq'])->delete();
    }
};
