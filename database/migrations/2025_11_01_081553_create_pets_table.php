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
    Schema::create('pets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // 👈 добавь это
        $table->foreignId('animal_id')->constrained('animals')->cascadeOnDelete();
        $table->string('name');
        $table->date('birth_date')->nullable();
        $table->string('gender');
        $table->integer('age')->nullable();
        $table->string('color')->nullable();
        $table->string('photo')->nullable();
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
        Schema::dropIfExists('pets');
    }
};
