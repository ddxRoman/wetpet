<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Всем существующим городам ставим verified = 'confirmed'.
 * Новые города, добавленные пользователями, по-прежнему создаются как 'unconfirmed'
 * и попадают в админке на проверку.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('cities')
            ->where(function ($q) {
                $q->whereNull('verified')->orWhere('verified', '!=', 'confirmed');
            })
            ->update(['verified' => 'confirmed']);
    }

    public function down(): void
    {
        // Исходные статусы не сохранялись — откатывать нечего.
    }
};
