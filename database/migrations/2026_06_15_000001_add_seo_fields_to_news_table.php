<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('seo_title')->nullable()->after('slug')
                ->comment('SEO заголовок — если пусто, берётся title');
            $table->string('seo_description', 320)->nullable()->after('seo_title')
                ->comment('SEO описание — если пусто, берётся excerpt или начало content');
            $table->string('og_image')->nullable()->after('seo_description')
                ->comment('OG-картинка для соцсетей — если пусто, берётся image');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description', 'og_image']);
        });
    }
};
