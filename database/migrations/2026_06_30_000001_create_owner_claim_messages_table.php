<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_claim_messages', function (Blueprint $table) {
            $table->id();

            // Полиморфная связь с ClinicOwner / OrganizationOwner / DoctorOwner / SpecialistOwner
            $table->morphs('claimable'); // claimable_type + claimable_id

            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // кто написал
            $table->boolean('is_admin')->default(false); // от админа или от владельца
            $table->text('message');
            $table->boolean('is_read')->default(false); // прочитано получателем

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_claim_messages');
    }
};
