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
            'storage/doctors/doctor1.jpg',
            'storage/doctors/doctor2.jpg',
            'storage/doctors/doctor3.webp',
            'storage/doctors/doctor11.webp',
            'storage/doctors/doctor12.webp',
            'storage/doctors/doctor13.webp',
            'storage/doctors/doctor4.jpg',
            'storage/doctors/doctor5.jpg',
            'storage/doctors/doctor6.jpg',
            'storage/doctors/doctor8.jpg',
            'storage/doctors/doctor9.jpg',
            'storage/doctors/doctor10.jpg',
            'storage/doctors/doctor14.jpg',
            'storage/doctors/doctor7.webp',

        ];

        foreach (range(1, 55) as $i) {
            DB::table('doctors')->insert([
                'name' => $faker->name,
                'date_of_birth'    => $faker->dateTimeBetween('-60 years', '-25 years')->format('Y-m-d'),
                'city_id'             => $faker->numberBetween(1, 30),
                'specialization' => $faker->randomElement(['Терапевт', 'Хирург', 'Офтальмолог', 'Кардиолог','Узист', 'Хирург', 'Терапевт', 'Стоматолог']),
                'clinic_id' => rand(1, 8),
                'experience'       => $faker->numberBetween(1, 30) . ' лет',
                'exotic_animals'   => $faker->boolean() ? 'Да' : 'Нет',
                'On-site_assistance' => $faker->boolean() ? 'Да' : 'Нет',
                'photo' => 'doctors/doctor' . rand(1, 14) . '.webp',
                'description'      => $faker->realText(200),
                'created_at'       => now(),
                'updated_at'       => now(),



                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
