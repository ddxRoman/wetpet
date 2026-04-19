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
    Schema::create('prices', function (Blueprint $table) {
        $table->id();
        // Ссылка на саму услугу из таблицы services
        $table->foreignId('service_id')->constrained()->onDelete('cascade');
        
        // Цена и валюта
        $table->decimal('price', 10, 2)->nullable();
        $table->string('currency')->default('₽');

        /**
         * ПОЛИМОРФНЫЕ ПОЛЯ
         * priceable_id (INT) - будет хранить ID клиники или врача
         * priceable_type (STRING) - будет хранить путь к модели (напр. 'App\Models\Specialist')
         */
        $table->morphs('priceable'); 

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
