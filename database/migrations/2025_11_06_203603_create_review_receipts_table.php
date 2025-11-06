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
        Schema::create('review_receipts', function (Blueprint $table) {
            $table->id();
            
            // Связь с отзывом
            $table->foreignId('review_id')
                  ->constrained('reviews')
                  ->onDelete('cascade');

            // Связь с клиникой
            $table->foreignId('clinic_id')
                  ->constrained('clinics')
                  ->onDelete('cascade');

            // Путь к файлу чека
            $table->string('path');

            // Статус проверки (можно использовать для модерации)
            $table->enum('status', ['pending', 'verified', 'rejected'])
                  ->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_receipts');
    }
};
