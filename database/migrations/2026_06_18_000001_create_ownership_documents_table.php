<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ownership_documents', function (Blueprint $table) {
            $table->id();

            // Полиморфная связь: к какой именно owner-записи относится документ
            // (clinic_owners / organization_owners / doctor_owners / specialist_owners)
            $table->morphs('ownerable'); // ownerable_id, ownerable_type

            $table->string('path');               // путь в storage
            $table->string('original_name')->nullable(); // как файл назывался у пользователя
            $table->string('comment')->nullable(); // комментарий пользователя к документу

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ownership_documents');
    }
};
