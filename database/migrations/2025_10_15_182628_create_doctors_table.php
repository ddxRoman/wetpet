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
            $table->string('specialization'); // специализация врача
            $table->date('date_of_birth'); // специализация врача
            $table->string('city_id'); // город врача
            $table->string('clinic_id'); // клиника врача
            $table->string('experience'); // Опыт врача
            $table->string('exotic_animals'); // Опыт врача
            $table->string('On-site_assistance'); // Опыт врача
            $table->string('photo'); // Фото (путь к файлу)
            $table->text('description')->nullable(); // Описание, специализация и т.п.
            $table->timestamps(); // created_at и updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
