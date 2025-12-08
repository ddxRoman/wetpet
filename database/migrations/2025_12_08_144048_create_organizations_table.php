<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('organizations', function (Blueprint $table) {
        $table->id();

        // Основные данные
        $table->string('name');

        // Адрес
        $table->string('country');
        $table->string('region')->nullable();
        $table->string('city');
        $table->string('street');
        $table->string('house')->nullable();

        // Инфо
        $table->text('description')->nullable();
        $table->string('logo')->nullable();

        // График
        $table->string('schedule')->nullable();
        $table->string('workdays')->nullable();

        // Контакты
        $table->string('phone')->nullable();
        $table->string('email')->nullable();

        // Тип (берём из field_of_activities.activity)
        $table->string('type');

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
