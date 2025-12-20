<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClinicServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Очищаем pivot-таблицу
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('clinic_service')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('clinic_service')->insert([
            'clinic_id' => 1,
            'service_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('✅ Добавлена 1 связь clinic ↔ service.');
    }
}
