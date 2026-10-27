<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auto_renewals', function (Blueprint $table) {
            // Дата окончания подписки, для которой уже отправлено предупреждение о списании (чтобы не слать письмо повторно)
            $table->date('notified_for')->nullable()->after('last_attempt_at');
        });
    }

    public function down(): void
    {
        Schema::table('auto_renewals', function (Blueprint $table) {
            $table->dropColumn('notified_for');
        });
    }
};
