<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SpecialistSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('specialists')->insert([
            [
                'name' => 'Иван Петров',
                'specialization' => 'Ветеринар-терапевт',
                'date_of_birth' => '1985-04-12',
                'city_id' => '1',
                'organization_id' => '1',
                'experience' => 10,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Да',
                'photo' => 'specialists/ivan_petrov.jpg',
                'description' => 'Опытный ветеринар с уклоном в терапию домашних животных.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Анна Смирнова',
                'specialization' => 'Хирург',
                'date_of_birth' => '1990-07-22',
                'city_id' => '2',
                'organization_id' => '1',
                'experience' => 7,
                'exotic_animals' => 'Да',
                'On_site_assistance' => 'Нет',
                'photo' => 'specialists/anna_smirnova.jpg',
                'description' => 'Проводит сложные хирургические операции.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Сергей Иванов',
                'specialization' => 'Ортопед',
                'date_of_birth' => '1982-11-03',
                'city_id' => '3',
                'organization_id' => null,
                'experience' => 12,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Да',
                'photo' => 'specialists/sergey_ivanov.jpg',
                'description' => 'Специализируется на лечении опорно-двигательной системы.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Елена Кузнецова',
                'specialization' => 'Дерматолог',
                'date_of_birth' => '1988-02-15',
                'city_id' => '1',
                'organization_id' => '2',
                'experience' => 9,
                'exotic_animals' => 'Да',
                'On_site_assistance' => 'Нет',
                'photo' => 'specialists/elena_kuznetsova.jpg',
                'description' => 'Лечение кожных заболеваний у животных.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Дмитрий Орлов',
                'specialization' => 'Кардиолог',
                'date_of_birth' => '1979-09-30',
                'city_id' => '2',
                'organization_id' => '3',
                'experience' => 15,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Да',
                'photo' => 'specialists/dmitry_orlov.jpg',
                'description' => 'Диагностика и лечение заболеваний сердца.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}