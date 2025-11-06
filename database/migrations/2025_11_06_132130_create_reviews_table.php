<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            
            // Связь с пользователем
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Полиморфная связь — отзыв может быть к клинике, услуге, врачу и т.д.
            $table->morphs('reviewable');
            
            // Дата отзыва
            $table->date('review_date')->default(now());
            
            // Оценка (1–5)
            $table->unsignedTinyInteger('rating')->nullable();

            // Текст отзыва
            $table->text('content')->nullable();
            
            // Что понравилось / не понравилось
            $table->text('liked')->nullable();
            $table->text('disliked')->nullable();

            // 🧾 Чек — файл подтверждения
            $table->string('receipt_path')->nullable();
            $table->boolean('receipt_verified')->default(false);

            // 🐾 Информация о питомце
            $table->unsignedTinyInteger('pet_id')->nullable();

            
            $table->timestamps();
        });

        // Таблица с фотографиями к отзывам

    Schema::create('review_photos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('review_id')->constrained()->onDelete('cascade');
        $table->string('photo_path');
        $table->timestamps();
    });
}


    public function down(): void
    {
        Schema::dropIfExists('review_photos');
        Schema::dropIfExists('reviews');
    }
};
