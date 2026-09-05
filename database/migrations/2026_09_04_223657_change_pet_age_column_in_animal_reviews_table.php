<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Меняем pet_age с integer на decimal(4,1), чтобы можно было
     * хранить возраст в годах с одним знаком после запятой
     * (например: 0.5 — полгода, 1.5 — полтора года).
     */
    public function up(): void
    {
        Schema::table('animal_reviews', function (Blueprint $table) {
            $table->decimal('pet_age', 4, 1)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('animal_reviews', function (Blueprint $table) {
            $table->integer('pet_age')->nullable()->change();
        });
    }
};
