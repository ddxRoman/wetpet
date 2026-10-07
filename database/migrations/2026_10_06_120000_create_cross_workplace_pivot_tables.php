<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Перекрёстные места работы:
 *  - врач может работать не только в клиниках (clinic_doctor), но и в организациях;
 *  - специалист может работать не только в организациях (organization_specialist), но и в клиниках.
 *
 * Колонки doctors.clinic_id и specialists.organization_id по-прежнему означают
 * «основное место работы своего типа» (по ним строятся slug и старые формы) и
 * этими таблицами не затрагиваются: врач, работающий только в организации,
 * просто имеет clinic_id = NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_organization', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['doctor_id', 'organization_id']);
        });

        Schema::create('clinic_specialist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialist_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['clinic_id', 'specialist_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_specialist');
        Schema::dropIfExists('doctor_organization');
    }
};
