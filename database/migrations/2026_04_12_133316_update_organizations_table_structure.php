<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // 1. Сначала удаляем все поля, которые стоят не на своих местах 
            // или имеют неправильные имена/типы.
            // Внимание: данные в этих колонках будут удалены!
            $table->dropColumn([
                'country', 'region', 'city', 'street', 'house', 'address_comment',
                'logo', 'description', 'phone', 'phone1', 'phone2', 'email', 
                'telegram', 'whatsapp', 'website', 'schedule', 'workdays', 'type'
            ]);
        });

        Schema::table('organizations', function (Blueprint $table) {
            // 2. Создаем их заново в СТРОГОЙ последовательности после slug
            $table->string('country')->after('slug');
            $table->string('region')->nullable()->after('country');
            $table->string('city')->after('region');
            $table->string('street')->after('city');
            $table->string('house')->nullable()->after('street');
            $table->string('address_comment')->nullable()->after('house');

            $table->string('logo')->nullable()->after('address_comment');
            $table->text('description')->nullable()->after('logo');

            $table->string('phone1')->nullable()->after('description');
            $table->string('phone2')->nullable()->after('phone1');
            $table->string('email')->nullable()->after('phone2');
            $table->string('telegram')->nullable()->after('email');
            $table->string('whatsapp')->nullable()->after('telegram');
            $table->string('website')->nullable()->after('whatsapp');

            $table->string('schedule')->nullable()->after('website');
            $table->string('workdays')->nullable()->after('schedule');

            // Твой type ставим в самый конец перед таймстемпами
            $table->string('type')->after('workdays');
            
            // Добавляем индекс для "ключика"
            $table->index('city');
        });
    }

    public function down(): void
    {
        // В методе down можно просто оставить удаление индекса
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex(['city']);
        });
    }
};