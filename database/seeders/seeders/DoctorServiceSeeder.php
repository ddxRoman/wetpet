<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\Service;


class DoctorServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Очистим таблицу перед заполнением
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
DB::table('doctor_service')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        $doctors = Doctor::all();
        $services = Service::all();

        if ($doctors->isEmpty() || $services->isEmpty()) {
            $this->command->warn('⚠️ Нет докторов или услуг — сидер пропущен.');
            return;
        }

        $insertData = [];

        foreach ($doctors as $doctor) {
            // Каждому доктору даём от 3 до 8 случайных услуг
            $assignedServices = $services->random(rand(3, 8));

            foreach ($assignedServices as $service) {
                $insertData[] = [
                    'doctor_id' => $doctor->id,
                    'service_id' => $service->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Вставляем все связи одной пачкой
        DB::table('doctor_service')->insert($insertData);

        $this->command->info('✅ Таблица doctor_service успешно заполнена. Создано связей: ' . count($insertData));
    }
}
