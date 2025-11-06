<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();

            // Внешние ключи
            $table->foreignId('clinic_id')
                ->constrained('clinics')
                ->onDelete('cascade');

            $table->foreignId('service_id')
                ->constrained('services')
                ->onDelete('cascade');

            // Цена услуги в конкретной клинике
            $table->decimal('price', 10, 2)->nullable();

            // Доп. информация
            $table->string('currency', 10)->default('RUB');

            $table->timestamps();

            // Один сервис может иметь только одну цену в пределах одной клиники
            $table->unique(['clinic_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
