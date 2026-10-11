<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trusted_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();         // кто добавил
            $table->foreignId('trusted_user_id')->constrained('users')->cascadeOnDelete(); // кого добавили
            $table->timestamps();

            $table->unique(['user_id', 'trusted_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trusted_users');
    }
};
