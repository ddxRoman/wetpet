<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'premium_until')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('premium_until')->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'premium_until')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('premium_until');
            });
        }
    }
};
