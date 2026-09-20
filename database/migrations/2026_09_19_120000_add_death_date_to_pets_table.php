<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('pets', 'death_date')) {
            return;
        }

        Schema::table('pets', function (Blueprint $table) {
            $table->date('death_date')->nullable()->after('birth_date');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('pets', 'death_date')) {
            Schema::table('pets', function (Blueprint $table) {
                $table->dropColumn('death_date');
            });
        }
    }
};
