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

    Schema::create('specialist_contacts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('specialist_id')->constrained()->onDelete('cascade');
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->boolean('telegram')->default(false);
        $table->boolean('whatsapp')->default(false);
        $table->boolean('max')->default(false); // Твой мессенджер VK/Max
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specialist_contacts');
    }
};
