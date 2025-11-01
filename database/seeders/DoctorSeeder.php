<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ru_RU');

        $photos = [
            'images/doctors/doctor1.jpg',
            'images/doctors/doctor2.jpg',
            'images/doctors/doctor3.jpg',
            'images/doctors/doctor4.jpg',
            'images/doctors/doctor5.jpg',
            'images/doctors/doctor6.jpg',
            'images/doctors/doctor7.webp',
        ];

        foreach (range(1, 15) as $i) {
            DB::table('doctors')->insert([
                'name' => $faker->name,
                'specialization' => $faker->randomElement(['Терапевт', 'Хирург', 'Офтальмолог', 'Кардиолог']),
                'clinic' => $faker->randomElement(['Биосфера', 'Слон', 'ВетЛазарет', 'Государственная']),
                'photo' => $faker->randomElement($photos),
                'description' => $faker->randomElement(['Хороший', 'Плохой', 'Злой', 'Добрый']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
