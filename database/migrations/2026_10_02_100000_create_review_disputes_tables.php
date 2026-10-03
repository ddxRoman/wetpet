<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Обжалование отзывов владельцами карточек.
 *
 * reviews.disputed_at            — отзыв скрыт до выяснения обстоятельств (NULL — виден всем)
 * review_disputes                — само обжалование (кто оспорил, статус, причина)
 * review_dispute_messages        — переписка с админом; party = owner|author — это два отдельных диалога
 *                                  (админ ↔ владелец карточки и админ ↔ автор отзыва)
 * review_dispute_files           — файлы, приложенные к сообщениям
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->timestamp('disputed_at')->nullable()->index();
        });

        Schema::create('review_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->cascadeOnDelete();
            $table->foreignId('opened_by')->constrained('users')->cascadeOnDelete(); // владелец карточки
            $table->string('status', 20)->default('open');   // open | restored | removed
            $table->text('reason')->nullable();               // причина, которую указал владелец
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['review_id', 'status']);
        });

        Schema::create('review_dispute_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_dispute_id')->constrained()->cascadeOnDelete();
            $table->string('party', 10);                      // owner | author
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // кто написал
            $table->boolean('is_admin')->default(false);
            $table->text('message')->nullable();
            $table->boolean('is_read')->default(false);       // прочитано получателем
            $table->timestamps();

            $table->index(['review_dispute_id', 'party']);
        });

        Schema::create('review_dispute_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_dispute_message_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_dispute_files');
        Schema::dropIfExists('review_dispute_messages');
        Schema::dropIfExists('review_disputes');

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('disputed_at');
        });
    }
};
