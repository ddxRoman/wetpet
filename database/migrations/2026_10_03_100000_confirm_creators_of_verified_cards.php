<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Уже верифицированные карточки: запись владельца-создателя остаётся с is_confirmed = 0,
 * из-за чего на странице карточки висит «На проверке». Подтверждаем такие записи.
 * Заявки других пользователей («Это я») и отклонённые заявки не трогаем.
 */
return new class extends Migration
{
    public function up(): void
    {
        $pairs = [
            ['organization_owners', 'organization_id', 'organizations'],
            ['clinic_owners',       'clinic_id',       'clinics'],
            ['doctor_owners',       'doctor_id',       'doctors'],
            ['specialist_owners',   'specialist_id',   'specialists'],
        ];

        foreach ($pairs as [$ownerTable, $fk, $cardTable]) {
            if (! Schema::hasTable($ownerTable) || ! Schema::hasColumn($cardTable, 'created_by') || ! Schema::hasColumn($cardTable, 'is_verified')) {
                continue;
            }

            DB::table($ownerTable . ' as ow')
                ->join($cardTable . ' as c', 'c.id', '=', 'ow.' . $fk)
                ->where('c.is_verified', true)
                ->whereColumn('ow.user_id', 'c.created_by')
                ->where('ow.is_confirmed', false)
                ->where(function ($q) {
                    $q->where('ow.is_rejected', false)->orWhereNull('ow.is_rejected');
                })
                ->update(['ow.is_confirmed' => true]);
        }
    }

    public function down(): void
    {
        // Прежние значения не сохранялись — откатывать нечего.
    }
};
