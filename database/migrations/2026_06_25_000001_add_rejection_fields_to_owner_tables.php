<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'specialist_owners',
            'doctor_owners',
            'clinic_owners',
            'organization_owners',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->boolean('is_rejected')->default(false)->after('is_confirmed');
                $t->timestamp('rejected_at')->nullable()->after('is_rejected');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'specialist_owners',
            'doctor_owners',
            'clinic_owners',
            'organization_owners',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['is_rejected', 'rejected_at']);
            });
        }
    }
};
