<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewSeeder extends Seeder
{
    /**
     * Запуск сидера
     */
    public function run(): void
    {
        // Отключаем внешние ключи и очищаем таблицу
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('doctors')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $doctors = [
    [
        'name' => 'Аношкин Сергей',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Офтальмолог, терапевт',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Ефименко Екатерина',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Врач УЗ-диагностики',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Чечель Ирина',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Терапевт',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Коноплева Кристина',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Терапевт, нефролог',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Юшков Дмитрий',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Терапевт, невролог, ортопед',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Баринянц Кристина',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Терапевт, кардиолог',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Плакса Анастасия',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Терапевт, хирург, стоматолог',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Никитина Екатерина',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Терапевт',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Гай Майя',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Терапевт, врач УЗ-диагностики',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Левицкий Алексей',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Хирургия, ортопедия',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Яковлева Анастасия',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Терапевт',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Щеткина Полина',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Терапевт',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Клименко Надежда',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Терапевт, эндокринолог',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Ахинян Виктор',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Хирург',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Клещевская Светлана',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Кардиолог, пульмонолог',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Эн-Наджджари Ольга',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Терапевт',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Резникова Алиса',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Врач-дерматолог',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Фокина Анна',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Главный врач, кардиолог, врач УЗ-диагностики',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Охотникова Виктория',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Терапевт',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Балкина Анастасия',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Терапевт',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Патерикина Ксения',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Терапевт',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],
    [
        'name' => 'Лукьянченко Даниил',
        'date_of_birth' => NULL,
        'city_id' => 31,
        'specialization' => 'Терапевт',
        'clinic_id' => 66,
        'experience' => NULL,
        'exotic_animals' => NULL,
        'On_site_assistance' => NULL,
        'photo' => NULL,
        'description' => '',
    ],

        ];

        foreach ($doctors as $doctor) {

            $slugParts = [
                $doctor['name'],
            ];

            // если есть clinic_id — добавляем название клиники
            if (!empty($doctor['clinic_id'])) {
                $clinicName = DB::table('clinics')
                    ->where('id', $doctor['clinic_id'])
                    ->value('name');

                if ($clinicName) {
                    $slugParts[] = $clinicName;
                }
            }

            $slug = Str::slug(implode(' ', $slugParts));

            $experience = $doctor['experience'] ?? null;
            unset($doctor['experience']);

            DB::table('doctors')->insert([
                ...$doctor,
                'practice_started_at' => $experience !== null
                    ? now()->startOfMonth()->subYears((int) $experience)->toDateString()
                    : null,
                'slug' => $slug,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
