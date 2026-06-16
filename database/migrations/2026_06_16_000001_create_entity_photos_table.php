<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_photos', function (Blueprint $table) {
            $table->id();
            // polymorphic: clinic, organization, doctor, specialist
            $table->morphs('photoable');
            $table->string('path');              // путь в storage
            $table->string('caption')->nullable(); // подпись к фото
            $table->integer('sort_order')->default(0);
            $table->boolean('is_main')->default(false); // главное фото
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_photos');
    }
};
