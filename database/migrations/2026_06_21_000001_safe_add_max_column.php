<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Clinics: добавляем колонку max, если её ещё нет ──
        if (!Schema::hasColumn('clinics', 'max')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->string('max')->nullable()->after('whatsapp');
            });
        }

        // ── Organizations: добавляем колонку max, если её ещё нет ──
        if (!Schema::hasColumn('organizations', 'max')) {
            Schema::table('organizations', function (Blueprint $table) {
                $table->string('max')->nullable()->after('whatsapp');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('clinics', 'max')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->dropColumn('max');
            });
        }

        if (Schema::hasColumn('organizations', 'max')) {
            Schema::table('organizations', function (Blueprint $table) {
                $table->dropColumn('max');
            });
        }
    }
};
