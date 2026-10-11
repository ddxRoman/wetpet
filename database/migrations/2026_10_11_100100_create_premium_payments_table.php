<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('premium_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('plan', 20);                       // month | year
            $table->unsignedInteger('amount');                // рубли
            $table->string('currency', 3)->default('RUB');
            $table->string('status', 20)->default('pending'); // pending | paid | canceled
            $table->string('gateway', 30)->nullable();
            $table->string('external_id')->nullable();        // id платежа в платёжной системе
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('premium_payments');
    }
};
