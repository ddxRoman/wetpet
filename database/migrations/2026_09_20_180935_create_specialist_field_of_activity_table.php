<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specialist_field_of_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialist_id')->constrained()->onDelete('cascade');
            $table->foreignId('field_of_activity_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['specialist_id', 'field_of_activity_id'], 'specialist_field_of_activity_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialist_field_of_activity');
    }
};
