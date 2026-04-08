<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('specialists', function (Blueprint $table) {
        // Добавляем nullable, чтобы старые записи не выдали ошибку при создании поля
        $table->string('slug')->unique()->nullable()->after('name');
    });
}

public function down(): void
{
    Schema::table('specialists', function (Blueprint $table) {
        $table->dropColumn('slug');
    });
}
};