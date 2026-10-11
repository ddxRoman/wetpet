<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('pets', 'caption')) {
            return;
        }

        Schema::table('pets', function (Blueprint $table) {
            $table->text('caption')->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('pets', 'caption')) {
            Schema::table('pets', function (Blueprint $table) {
                $table->dropColumn('caption');
            });
        }
    }
};
