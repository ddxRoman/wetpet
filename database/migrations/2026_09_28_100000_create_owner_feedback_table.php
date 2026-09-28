<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_feedback', function (Blueprint $table) {
            $table->id();

            // Пользователь (если его удалят — сообщение остаётся, имя хранится отдельно)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();

            // Что удалено. Данные хранятся копией, т.к. сама карточка после удаления пропадает из БД
            $table->string('entity_type', 32);          // clinic | organization | doctor | specialist
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('entity_name')->nullable();
            $table->string('activity_type')->nullable(); // тип организации / специализация
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->json('snapshot')->nullable();        // все поля карточки на момент удаления

            // Причина удаления от пользователя
            $table->text('reason');

            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_feedback');
    }
};
