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
            $table->date('date_of_birth')->nullable(); // специализация врача
            $table->string('city_id'); // город врача
            $table->string('organization_id')->nullable(); // организация специалиста
            $table->integer('experience')->nullable(); // Опыт 
            $table->string('exotic_animals')->nullable(); // Экзотические животные
            $table->string('On_site_assistance')->nullable(); // Выезд на дом
            $table->string('photo')->nullable(); // Фото (путь к файлу)
            $table->text('description')->nullable(); // Описание, специализация и т.п.
            $table->timestamps(); // created_at и updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialists');
    }
};
