<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('legal_pages', function (Blueprint $table) {
        $table->id();
        $table->string('slug')->unique(); // например, 'privacy-policy' или 'terms'
        $table->string('title');          // Заголовок: 'Политика конфиденциальности'
        $table->string('subtitle')->nullable(); // 'Действующая редакция...'
        $table->text('intro')->nullable(); // Текст в серой плашке
        $table->longText('content');      // Основной текст (HTML)
        $table->string('meta_title')->nullable();
        $table->string('meta_description')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_pages');
    }
};
