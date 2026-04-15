<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('specialists', function (Blueprint $table) {
        $table->string('street')->nullable()->after('city_id'); // или просто after('name')
        $table->string('house')->nullable()->after('street');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('specialists', function (Blueprint $table) {
            //
        });
    }
};
