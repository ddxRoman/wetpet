<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PriceSeeder extends Seeder
{
    public function run(): void
    {
        // Отключаем проверку внешних ключей, чтобы не было ошибок при очистке
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('prices')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $clinicIds = DB::table('clinics')->pluck('id')->toArray();
        $serviceIds = DB::table('services')->pluck('id')->toArray();

        if (empty($clinicIds) || empty($serviceIds)) {
            $this->command->warn('⚠️ Нет данных в clinics или services — сидер PriceSeeder пропущен.');
            return;
        }

        $prices = [];

        foreach ($clinicIds as $clinicId) {
            // Для каждой клиники создаём случайные цены на 10–20 услуг
            $randomServices = collect($serviceIds)->shuffle()->take(rand(0, 200));

            foreach ($randomServices as $serviceId) {
                $prices[] = [
                    'clinic_id' => $clinicId,
                    'service_id' => $serviceId,
                    'price' => rand(500, 10000),
                    'currency' => 'RUB',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('prices')->insert($prices);
    }
}
