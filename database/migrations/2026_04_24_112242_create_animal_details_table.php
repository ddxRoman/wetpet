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
    Schema::create('animal_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('animal_id')->constrained('animals')->onDelete('cascade');
        
        // Характеристики
        $table->string('weight_range')->nullable(); 
        $table->string('height_range')->nullable(); 
        $table->string('lifespan')->nullable();     
        $table->string('type')->nullable();     
        $table->string('photo')->nullable();    
        
        // Текстовые блоки
        $table->text('short_description')->nullable(); 
        $table->longText('full_description')->nullable();

        // Гибкие параметры (JSON)
        $table->json('features')->nullable(); 

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animal_details');
    }
};
