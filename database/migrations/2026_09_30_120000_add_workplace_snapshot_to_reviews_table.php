<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Снимок места работы врача/специалиста на момент написания отзыва.
 *
 * Если отзыв оставлен врачу (reviewable_type=Doctor) или специалисту
 * (reviewable_type=Specialist), сюда записывается клиника/организация,
 * в которой он работал ИМЕННО В МОМЕНТ отзыва (workplace_type/workplace_id).
 * Это позволяет показывать такой отзыв и на странице клиники/организации
 * с пометкой «Отзыв о специалисте …», даже если потом он сменит место
 * работы — для старого места это будет видно через сравнение
 * workplace_id с текущим clinic_id/organization_id врача/специалиста.
 *
 * Для отзывов, оставленных напрямую клинике/организации (reviewable_type
 * = Clinic/Organization), эти поля остаются NULL — они не нужны.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('workplace_type')->nullable()->after('reviewable_type');
            $table->unsignedBigInteger('workplace_id')->nullable()->after('workplace_type');
            $table->index(['workplace_type', 'workplace_id']);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['workplace_type', 'workplace_id']);
            $table->dropColumn(['workplace_type', 'workplace_id']);
        });
    }
};
