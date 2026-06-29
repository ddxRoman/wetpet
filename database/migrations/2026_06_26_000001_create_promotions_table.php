<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();

            // Полиморфная связь с Clinic / Organization / Doctor / Specialist
            $table->morphs('promotable'); // promotable_type + promotable_id

            $table->string('title');                          // Название акции
            $table->text('description')->nullable();          // Описание
            $table->decimal('old_price', 10, 2)->nullable();  // Старая цена
            $table->decimal('new_price', 10, 2)->nullable();  // Новая цена
            $table->string('badge')->nullable();              // Текст бейджа (-30%, Топ цена и т.д.)
            $table->date('expires_at')->nullable();           // Срок действия
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        // Флаг рекламного пакета на пользователя
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_promo_package')->default(false)->after('is_admin');
            $table->date('promo_package_expires_at')->nullable()->after('has_promo_package');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['has_promo_package', 'promo_package_expires_at']);
        });
    }
};
