<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Адрес
            $table->string('country');
            $table->string('region')->nullable();
            $table->string('city');
            $table->string('street');
            $table->string('house')->nullable();
            $table->string('address_comment')->nullable();

            $table->string('logo')->nullable();
            $table->text('description')->nullable();

            // Отношения по кодам
            $table->json('services')->nullable();
            $table->json('doctors')->nullable();

            // Контакты
            $table->string('phone1')->nullable();
            $table->string('phone2')->nullable();
            $table->string('email')->nullable();
            $table->string('telegram')->nullable();
            $table->string('whatsapp')->nullable();

            // Время работы
            $table->string('schedule')->nullable(); // "с 8:00 до 22:00"
            $table->string('workdays')->nullable(); // "Пн–Вс"

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
