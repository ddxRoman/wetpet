<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Price;
use App\Models\Service;
use App\Models\Specialist;
use App\Models\Organization;

class PriceSeeder extends Seeder
{
public function run(): void
{
    $models = [
        \App\Models\Clinic::class,
        \App\Models\Organization::class,
        \App\Models\Doctor::class,
        \App\Models\Specialist::class,
    ];

    foreach ($models as $modelClass) {
        $entities = $modelClass::all();
        
        foreach ($entities as $entity) {
            // Создаем по 3 случайные услуги для каждой сущности
            for ($i = 0; $i < 3; $i++) {
                \App\Models\Price::create([
                    'service_id' => \App\Models\Service::inRandomOrder()->first()->id,
                    'price' => rand(1000, 5000),
                    'currency' => '₽',
                    'priceable_id' => $entity->id,
                    'priceable_type' => $modelClass, // Запишет полный путь к модели
                ]);
            }
        }
    }
}
}