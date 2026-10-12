<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Подписка карточки на пакет продвижения (одна запись на карточку и пакет)
        Schema::create('boost_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 20);               // clinic | organization
            $table->unsignedBigInteger('entity_id');
            $table->string('package', 20);                   // manage | top | featured
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['entity_type', 'entity_id', 'package']);
        });

        // Платежи за пакеты
        Schema::create('boost_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('entity_type', 20);
            $table->unsignedBigInteger('entity_id');
            $table->string('package', 20);
            $table->string('period', 20);                    // month | year
            $table->unsignedInteger('amount');               // рубли
            $table->string('currency', 3)->default('RUB');
            $table->string('status', 20)->default('pending'); // pending | paid | canceled
            $table->string('gateway', 30)->nullable();
            $table->string('external_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boost_payments');
        Schema::dropIfExists('boost_subscriptions');
    }
};
