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
    Schema::create('animal_reviews', function (Blueprint $table) {
        $table->id();
        $table->foreignId('animal_id')->constrained('animals')->onDelete('cascade');
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        
        // Данные о питомце автора
        $table->integer('pet_weight')->nullable();
        $table->integer('pet_age')->nullable();

        // Оценки (выпадающие списки)
        $table->string('temperament'); 
        $table->integer('trainability'); 
        $table->integer('intelligence'); 
        $table->integer('sociability');  
        
        $table->text('comment'); 
        $table->json('health_issues')->nullable(); // Хронические заболевания конкретного питомца
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animal_reviews');
    }
};
