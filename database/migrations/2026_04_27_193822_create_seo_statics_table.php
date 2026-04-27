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
    Schema::create('seo_statics', function (Blueprint $table) {
        $table->id();
        $table->string('route_name')->unique()->nullable(); // Например: clinics.index
        $table->string('url_path')->unique()->nullable();   // Например: /login
        $table->string('title');
        $table->string('description');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_statics');
    }
};
