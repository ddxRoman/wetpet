<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['doctors', 'specialists'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->boolean('works_online')->default(false)->index();
            });
        }
    }

    public function down(): void
    {
        foreach (['doctors', 'specialists'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropIndex(['works_online']);
                $t->dropColumn('works_online');
            });
        }
    }
};
