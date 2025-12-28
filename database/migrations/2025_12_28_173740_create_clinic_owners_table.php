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
// database/migrations/xxxx_xx_xx_create_clinic_owners_table.php

Schema::create('clinic_owners', function (Blueprint $table) {
    $table->id();

    $table->foreignId('clinic_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('user_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->boolean('is_confirmed')->default(true);

    $table->timestamps();

    $table->unique(['clinic_id', 'user_id']);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_owners');
    }
};
