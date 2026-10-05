<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Уведомления на сайте (модальное окно поверх страницы), которыми управляет админ:
 * текст и/или картинка, расписание и частота показа, аудитория, таймер кнопки закрытия.
 *
 * site_notices       — сами уведомления
 * site_notice_views  — когда и сколько раз уведомление показано конкретному посетителю
 *                      (visitor_key: u:{id} для вошедших, g:{uuid} для гостей) — по ним считается частота
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_notices', function (Blueprint $table) {
            $table->id();

            // Содержимое
            $table->string('title', 150)->nullable();
            $table->text('body')->nullable();
            $table->string('image')->nullable();

            // Включено / приоритет (выше — показывается раньше)
            $table->boolean('is_active')->default(true)->index();
            $table->integer('priority')->default(0);

            // Когда показывать: период и «окно» внутри суток (по времени сайта, см. SiteNotice::TIMEZONE)
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->time('daily_from')->nullable();
            $table->time('daily_until')->nullable();

            // Как часто: once | session | hours | days | always
            $table->string('frequency_type', 10)->default('once');
            $table->unsignedSmallInteger('frequency_value')->nullable();

            // Кому: all | auth | guests; плюс фильтры (все заданные условия должны выполниться)
            $table->string('user_scope', 10)->default('all');
            $table->json('species')->nullable();     // владельцы питомцев этих видов (animals.species)
            $table->json('regions')->nullable();     // жители этих регионов
            $table->json('city_ids')->nullable();    // жители этих городов (регион ИЛИ город)

            // Через сколько секунд появится крестик закрытия
            $table->unsignedSmallInteger('close_delay')->default(0);

            $table->timestamps();
        });

        Schema::create('site_notice_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_notice_id')->constrained('site_notices')->cascadeOnDelete();
            $table->string('visitor_key', 80);
            $table->timestamp('last_shown_at')->nullable();
            $table->unsignedInteger('shown_count')->default(0);
            $table->timestamps();

            $table->unique(['site_notice_id', 'visitor_key']);
            $table->index('visitor_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_notice_views');
        Schema::dropIfExists('site_notices');
    }
};
