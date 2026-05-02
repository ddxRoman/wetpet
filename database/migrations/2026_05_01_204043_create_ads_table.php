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
    Schema::create('ads', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Автор
        $table->string('title');
        $table->text('description');
        
        // Цена
        $table->decimal('price', 10, 2)->nullable();
        $table->enum('price_type', ['fixed', 'free', 'exchange'])->default('fixed');
        
        $table->string('phone');
        $table->string('city');
        $table->string('address')->nullable();
        $table->enum('condition', ['new', 'used']);
        
        // Тип животного из твоей таблицы animals
        $table->foreignId('animal_id')->constrained('animals'); 
        
        $table->json('photos')->nullable(); // До 5 штук
        $table->boolean('is_active')->default(true); // Для удаления пользователем
        $table->timestamp('moderated_at')->nullable(); // Для админки
        
        $table->softDeletes(); // Сохранение в базе навсегда
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
