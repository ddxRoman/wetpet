<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Pet;
use App\Models\User;
use App\Models\Animal;

class PetSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('ru_RU');
        $userIds = User::pluck('id')->toArray();
        $animalIds = Animal::pluck('id')->toArray();

        if (empty($animalIds)) {
            $this->command->warn('⚠️ Таблица animals пуста. Сначала запусти AnimalSeeder.');
            return;
        }

        foreach (range(1, 30) as $i) {
            Pet::create([
                'user_id'    => $faker->randomElement($userIds),
                'animal_id'  => $faker->randomElement($animalIds),
                'name'       => $faker->firstName,
                'birth_date' => $faker->date(),
                'color'      => $faker->safeColorName,
                'gender' => $faker->randomElement(['Самец', 'Самка']),
                'photo'      => null,
            ]);
        }
    }
}
