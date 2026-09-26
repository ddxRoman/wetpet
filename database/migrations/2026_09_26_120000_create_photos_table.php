<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();

            // Полиморфная связь: клиники, организации, врачи, специалисты
            $table->morphs('photoable');

            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['photoable_type', 'photoable_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
