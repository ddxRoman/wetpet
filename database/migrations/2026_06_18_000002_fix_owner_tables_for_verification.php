<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // clinic_owners был default(true) — новые заявки автоматически считались
        // подтверждёнными. Меняем на false, чтобы все новые проходили проверку.
        Schema::table('clinic_owners', function (Blueprint $table) {
            $table->boolean('is_confirmed')->default(false)->change();
            if (!Schema::hasColumn('clinic_owners', 'admin_comment')) {
                $table->string('admin_comment')->nullable()->after('is_confirmed');
            }
        });

        foreach (['organization_owners', 'doctor_owners', 'specialist_owners'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'admin_comment')) {
                    $table->string('admin_comment')->nullable()->after('is_confirmed');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('clinic_owners', function (Blueprint $table) {
            $table->boolean('is_confirmed')->default(true)->change();
            $table->dropColumn('admin_comment');
        });

        foreach (['organization_owners', 'doctor_owners', 'specialist_owners'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('admin_comment');
            });
        }
    }
};
