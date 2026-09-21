<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * В справочнике городов «Краснодарский край» и «Краснодарский Край» (а также
 * «Красноярский край» / «Красноярский Край») были записаны по-разному, из-за чего
 * в списке регионов появлялись дубли. Приводим написание к «… край».
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['cities', 'organizations', 'clinics'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'region')) {
                continue;
            }

            DB::table($table)
                ->where('region', 'like', '% Край')
                ->update(['region' => DB::raw("REPLACE(region, ' Край', ' край')")]);
        }
    }

    public function down(): void
    {
        // Возвращать неверное написание не нужно
    }
};
