<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('specialists', function (Blueprint $table) {
            $table->id(); // ID врача
            $table->string('name'); // Имя врача
            $table->string('specialization'); // специализация
            $table->date('date_of_birth'); // специализация врача
            $table->string('city_id'); // город врача
            $table->string('organization_id'); // организация специалиста
            $table->integer('experience'); // Опыт 
            $table->string('exotic_animals'); // Экзотические животные
            $table->string('On_site_assistance'); // Выезд на дом
            $table->string('photo'); // Фото (путь к файлу)
            $table->text('description')->nullable(); // Описание, специализация и т.п.
            $table->timestamps(); // created_at и updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialists');
    }
};
