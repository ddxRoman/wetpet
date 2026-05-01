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
        
        // Добавляем связь с таблицей типов деятельности
        $table->foreignId('field_of_activity_id')
              ->nullable() // Можно оставить nullable, если тип не обязателен
              ->constrained('field_of_activities')
              ->onDelete('set null'); // Если тип удалят, организация останется

        $table->string('name');
        $table->string('slug')->unique();
        
        // Адрес
        $table->string('country');
        $table->string('region')->nullable();
        $table->string('city');
        $table->string('street');
        $table->string('house')->nullable();
        $table->string('address_comment')->nullable();
        
        // Инфо
        $table->string('logo')->nullable();
        $table->text('description')->nullable();
        
        // Контакты
        $table->string('phone1')->nullable();
        $table->string('phone2')->nullable();
        $table->string('email')->nullable();
        $table->string('telegram')->nullable();
        $table->string('whatsapp')->nullable();
        $table->string('website')->nullable();
        
        // График
        $table->string('schedule')->nullable();
        $table->string('workdays')->nullable();
        $table->string('seo_title')->nullable();
        $table->text('seo_description')->nullable();
        
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
