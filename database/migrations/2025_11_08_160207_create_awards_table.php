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
    Schema::create('awards', function (Blueprint $table) {
        $table->id();
        $table->foreignId('clinic_id')->constrained()->onDelete('cascade');
        $table->string('title')->nullable(); // краткое описание
        $table->text('description')->nullable(); // подробное описание
        $table->string('image'); // путь к фото награды
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('awards');
    }
};
