<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinic;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class ClinicServiceSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
DB::table('clinic_service')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        $clinics = Clinic::all();
        $services = Service::all();

        if ($clinics->isEmpty() || $services->isEmpty()) {
            $this->command->warn('⚠️ Нет данных в clinics или services — сидер пропущен.');
            return;
        }

        $insert = [];

        foreach ($clinics as $clinic) {
            // Каждая клиника получит от 5 до 15 случайных услуг
            $assignedServices = $services->random(rand(50, 150));
            foreach ($assignedServices as $service) {
                $insert[] = [
                    'clinic_id' => $clinic->id,
                    'service_id' => $service->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('clinic_service')->insert($insert);

        $this->command->info('✅ Таблица clinic_service успешно заполнена (' . count($insert) . ' связей).');
    }
}
