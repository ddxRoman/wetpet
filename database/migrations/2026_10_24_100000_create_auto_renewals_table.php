<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Автопродление: одна запись на Премиум пользователя или на пакет продвижения карточки
        Schema::create('auto_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('scope_key')->unique();            // premium:{user} | boost:{type}:{id}:{package}
            $table->string('kind', 10);                       // premium | boost
            $table->string('entity_type', 20)->nullable();    // только для boost
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('package', 20)->nullable();        // только для boost: manage | top | featured
            $table->string('plan', 20);                       // month | year (тариф Премиума / период пакета)
            $table->string('payment_method_id')->nullable();  // сохранённый способ оплаты в ЮKassa (null после отключения)
            $table->string('payment_method_title')->nullable(); // например «Bank card *4444»
            $table->string('status', 20)->default('active');  // active | canceled | failed
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'kind']);
        });

        foreach (['premium_payments', 'boost_payments'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->boolean('auto_renew')->default(false);  // пользователь согласился на автопродление при оплате
                $table->boolean('is_renewal')->default(false);  // платёж создан автоматически (списание по подписке)
            });
        }
    }

    public function down(): void
    {
        foreach (['premium_payments', 'boost_payments'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn(['auto_renew', 'is_renewal']);
            });
        }

        Schema::dropIfExists('auto_renewals');
    }
};
