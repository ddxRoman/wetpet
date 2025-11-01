<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Pet;
use App\Models\User;

class PetSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ru_RU');
        $userIds = User::pluck('id')->toArray();

        foreach (range(1, 30) as $i) {
            Pet::create([
                'user_id' => $faker->randomElement($userIds),
                'type' => $faker->randomElement(['Кошка', 'Собака', 'Попугай', 'Хомяк']),
                'breed' => $faker->word,
                'birth_date' => $faker->date(),
                'color' => $faker->safeColorName,
                'photo' => null,
            ]);
        }
    }
}
