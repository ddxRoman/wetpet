<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Сети филиалов. Клиники и организации с одинаковым chain_id — филиалы одной сети;
 * в карточке показываются ссылки на остальные филиалы из того же города.
 * При удалении сети филиалы остаются (chain_id → NULL).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chains', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('clinics', function (Blueprint $table) {
            $table->foreignId('chain_id')->nullable()->after('id')
                ->constrained('chains')->nullOnDelete();
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->foreignId('chain_id')->nullable()->after('id')
                ->constrained('chains')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('chain_id');
        });

        Schema::table('clinics', function (Blueprint $table) {
            $table->dropConstrainedForeignId('chain_id');
        });

        Schema::dropIfExists('chains');
    }
};
