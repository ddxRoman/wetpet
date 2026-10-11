<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Отдельная страница для каждого термина глоссария (/glossary/{slug}) со своим SEO:
 *  - slug — адрес страницы;
 *  - seo_title / seo_description — ручное SEO термина (необязательно);
 *  - запись 'glossary_term' в seo_catalog_pages — общий шаблон SEO для терминов,
 *    у которых ручное SEO не указано (редактируется в Filament: «SEO Каталожных страниц»).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('glossary_terms', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('term');
            $table->string('seo_title')->nullable()->after('is_active');
            $table->text('seo_description')->nullable()->after('seo_title');
        });

        // Slug для уже существующих терминов
        $used = [];
        foreach (DB::table('glossary_terms')->orderBy('id')->get(['id', 'term']) as $row) {
            $base = Str::slug($row->term) ?: 'termin';
            $slug = $base;
            $i = 2;
            while (isset($used[$slug])) {
                $slug = $base . '-' . $i++;
            }
            $used[$slug] = true;

            DB::table('glossary_terms')->where('id', $row->id)->update(['slug' => $slug]);
        }

        $now = now();
        DB::table('seo_catalog_pages')->insert([
            'key'         => 'glossary_term',
            'label'       => 'Термины глоссария — общий шаблон (/glossary/{термин})',
            'title'       => '{term} — что это такое, определение термина | Зверозор',
            'description' => '{term} — {definition}',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('seo_catalog_pages')->where('key', 'glossary_term')->delete();

        Schema::table('glossary_terms', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'seo_title', 'seo_description']);
        });
    }
};
