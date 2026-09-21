<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Стаж (целое число лет) не увеличивался сам. Вместо него храним
 * год и месяц начала практики, а стаж считаем на лету.
 */
return new class extends Migration
{
    private array $tables = ['doctors', 'specialists'];

    public function up(): void
    {
        $currentMonth = now()->startOfMonth();

        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->date('practice_started_at')->nullable()->after('experience');
            });

            // Переносим существующий стаж: начало практики = текущий месяц минус N лет
            DB::table($tableName)
                ->whereNotNull('experience')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($tableName, $currentMonth) {
                    foreach ($rows as $row) {
                        DB::table($tableName)
                            ->where('id', $row->id)
                            ->update([
                                'practice_started_at' => $currentMonth->copy()->subYears((int) $row->experience)->toDateString(),
                            ]);
                    }
                });

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('experience');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('experience')->nullable();
            });

            DB::table($tableName)
                ->whereNotNull('practice_started_at')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($tableName) {
                    foreach ($rows as $row) {
                        DB::table($tableName)
                            ->where('id', $row->id)
                            ->update([
                                'experience' => now()->startOfMonth()->diffInYears(\Carbon\Carbon::parse($row->practice_started_at)),
                            ]);
                    }
                });

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('practice_started_at');
            });
        }
    }
};
