<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PriceSeeder extends Seeder
{
    public function run(): void
    {
        // Очищаем таблицу
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('prices')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('prices')->insert([
            'clinic_id' => 1,
            'service_id' => 1,
            'price' => 700,
            'currency' => 'RUB',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('✅ Добавлена 1 запись в таблицу prices.');
    }
}
