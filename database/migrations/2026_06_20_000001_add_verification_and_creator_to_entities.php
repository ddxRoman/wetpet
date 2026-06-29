<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['doctors', 'organizations', 'clinics', 'specialists'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (!Schema::hasColumn($table, 'is_verified')) {
                    $t->boolean('is_verified')->default(false)->after('id');
                }
                if (!Schema::hasColumn($table, 'created_by')) {
                    $t->foreignId('created_by')->nullable()->after('is_verified')
                        ->constrained('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['doctors', 'organizations', 'clinics', 'specialists'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (Schema::hasColumn($table, 'created_by')) {
                    $t->dropConstrainedForeignId('created_by');
                }
                if (Schema::hasColumn($table, 'is_verified')) {
                    $t->dropColumn('is_verified');
                }
            });
        }
    }
};
