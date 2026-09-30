<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Врач может работать в нескольких клиниках, специалист — в нескольких организациях.
 *
 * Колонки doctors.clinic_id и specialists.organization_id остаются как «основное
 * место работы» (по ним строятся slug, поиск и старые формы), а полный список
 * мест работы хранится в этих сводных таблицах.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_doctor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['clinic_id', 'doctor_id']);
        });

        Schema::create('organization_specialist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialist_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['organization_id', 'specialist_id']);
        });

        // Переносим существующие связи «один к одному» в новые таблицы.
        // JOIN отсекает «битые» ссылки на уже удалённые клиники/организации.
        DB::statement(
            'INSERT INTO clinic_doctor (doctor_id, clinic_id, created_at, updated_at)
             SELECT d.id, c.id, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
             FROM doctors d
             JOIN clinics c ON c.id = d.clinic_id'
        );

        DB::statement(
            'INSERT INTO organization_specialist (specialist_id, organization_id, created_at, updated_at)
             SELECT s.id, o.id, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
             FROM specialists s
             JOIN organizations o ON o.id = s.organization_id'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_specialist');
        Schema::dropIfExists('clinic_doctor');
    }
};
