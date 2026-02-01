<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Запуск сидера
     */
    public function run(): void
    {
        // Очищаем таблицу
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('services')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $services = [
            [
                'name' => 'Первичный приём',
                'specialization' => 'Терапия',
                'specialization_doctor' => 'Терапевт',
            ],
            [
                'name' => 'Повторный приём',
                'specialization' => 'Терапия',
                'specialization_doctor' => 'Терапевт',
            ],
            [
                'name' => 'Общий осмотр',
                'specialization' => 'Терапия',
                'specialization_doctor' => 'Терапевт',
            ],
                        [
                'name' => 'Консультация',
                'specialization' => 'Терапия',
                'specialization_doctor' => 'Терапевт',
            ],
                        [
                'name' => 'Узи Сердца',
                'specialization' => 'Диагностика',
                'specialization_doctor' => 'Диагност',
            ],
                        [
                'name' => 'Узи брюшной полости',
                'specialization' => 'Диагностика',
                'specialization_doctor' => 'Диагност',
            ],
                        [
                'name' => 'Рентген грудной клетки',
                'specialization' => 'Рентген',
                'specialization_doctor' => 'Хирург',
            ],
                        [
                'name' => 'Рентген конечностей',
                'specialization' => 'Рентген',
                'specialization_doctor' => 'Хирург',
            ],
                        [
                'name' => 'Анализ крови общий',
                'specialization' => 'Диагностика',
                'specialization_doctor' => 'Диагност',
            ],
                        [
                'name' => 'Биохимический анализ крови ',
                'specialization' => 'Диагностика',
                'specialization_doctor' => 'Диагност',
            ],
        ];

        foreach ($services as $service) {
            Service::create([
                ...$service,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ Услуги успешно добавлены.');
    }
}
