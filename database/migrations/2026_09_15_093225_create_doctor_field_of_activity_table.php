<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_field_of_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('field_of_activity_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['doctor_id', 'field_of_activity_id'], 'doctor_field_of_activity_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_field_of_activity');
    }
};
