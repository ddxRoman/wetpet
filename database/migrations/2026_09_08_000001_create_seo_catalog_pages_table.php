<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seo_catalog_pages', function (Blueprint $table) {
            $table->id();
            // Уникальный технический ключ страницы/фильтра, используется в коде — не менять руками
            $table->string('key')->unique();
            // Человекочитаемое имя записи для админки
            $table->string('label');
            // Заголовок <title>, поддерживает переменные {city}, {specialization}, {activity_type}
            $table->string('title');
            // Meta description, поддерживает те же переменные
            $table->text('description');
            $table->timestamps();
        });

        $now = now();

        DB::table('seo_catalog_pages')->insert([
            [
                'key'         => 'doctors',
                'label'       => 'Врачи — каталог (без фильтров)',
                'title'       => 'Ветеринарные врачи в {city} — рейтинг и отзывы | Зверозор',
                'description' => 'Каталог ветеринарных врачей в {city}. Рейтинги, отзывы пациентов, специализации и контакты для записи на приём на Зверозор.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'doctors_specialization',
                'label'       => 'Врачи — фильтр по специализации',
                'title'       => '{specialization} в {city} — рейтинг врачей и отзывы | Зверозор',
                'description' => 'Ветеринарные врачи по специализации «{specialization}» в {city}. Рейтинги, отзывы пациентов, контакты для записи на приём на Зверозор.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'clinics',
                'label'       => 'Клиники — каталог',
                'title'       => 'Ветеринарные клиники в {city} — рейтинг и отзывы | Зверозор',
                'description' => 'Каталог ветеринарных клиник в {city}. Честные отзывы, рейтинги, услуги и контакты клиник на Зверозор.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'specialists',
                'label'       => 'Специалисты — каталог (без фильтров)',
                'title'       => 'Специалисты по животным в {city} — рейтинг и отзывы | Зверозор',
                'description' => 'Каталог специалистов по уходу за животными в {city}. Рейтинги, отзывы, специализации и контакты на Зверозор.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'specialists_specialization',
                'label'       => 'Специалисты — фильтр по специализации',
                'title'       => '{specialization} в {city} — рейтинг и отзывы | Зверозор',
                'description' => 'Специалисты «{specialization}» в {city}. Рейтинги, отзывы клиентов, контакты для записи на Зверозор.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'organizations',
                'label'       => 'Организации — каталог (без фильтров)',
                'title'       => 'Организации для животных в {city} — рейтинг и отзывы | Зверозор',
                'description' => 'Каталог организаций для животных в {city}. Отзывы, рейтинги и контакты на Зверозор.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'organizations_activity',
                'label'       => 'Организации — фильтр по типу деятельности',
                'title'       => '{activity_type} в {city} — рейтинг и отзывы | Зверозор',
                'description' => 'Организации «{activity_type}» в {city}. Отзывы клиентов, рейтинги и контакты на Зверозор.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'ads',
                'label'       => 'Объявления — каталог',
                'title'       => 'Объявления о животных в {city} | Зверозор',
                'description' => 'Объявления о животных в {city}: продажа, передача в добрые руки, вязка. Смотрите актуальные объявления на Зверозор.',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_catalog_pages');
    }
};
