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

    // Награда может принадлежать клинике
    $table->foreignId('clinic_id')
        ->nullable()
        ->constrained()
        ->onDelete('cascade');

    // Или врачу
    $table->foreignId('doctor_id')
        ->nullable()
        ->constrained()
        ->onDelete('cascade');

    $table->string('title')->nullable();
    $table->string('confirmed')->default('pending'); // pending / accepted / rejected
    $table->text('description')->nullable();
    $table->string('image');
$table->integer('sort')->default(0);

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
