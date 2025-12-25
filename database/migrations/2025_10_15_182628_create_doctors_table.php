<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id(); // ID врача
            $table->string('name'); // Имя врача
            $table->string('slug')->unique(); // Slug имени врача
            $table->string('specialization'); // специализация врача
            $table->date('date_of_birth')->nullable(); // Дата Рождения
            $table->string('city_id'); // город врача
            $table->string('clinic_id')->nullable(); // клиника врача
            $table->integer('experience')->nullable(); // Опыт врача
            $table->string('exotic_animals')->nullable(); // Экзотические животные
            $table->string('On_site_assistance')->nullable(); // Выезд на дом
            $table->string('photo')->nullable(); // Фото (путь к файлу)
            $table->text('description')->nullable(); // Описание, специализация и т.п.
            $table->timestamps(); // created_at и updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
