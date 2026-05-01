<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Organization;
use App\Models\Specialist;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Service;

class PriceSeeder extends Seeder
{
    public function run(): void
    {
        // Собираем ID всех возможных владельцев прайса
        $targets = [
            'App\Models\Organization' => Organization::pluck('id')->toArray(),
            'App\Models\Clinic'       => Clinic::pluck('id')->toArray(),
            'App\Models\Doctor'       => Doctor::pluck('id')->toArray(),
            'App\Models\Specialist'   => Specialist::pluck('id')->toArray(),
        ];

        // Убираем из списка те модели, в таблицах которых пусто
        $targets = array_filter($targets, fn($ids) => !empty($ids));
        
        $serviceIds = Service::pluck('id')->toArray();

        if (empty($serviceIds) || empty($targets)) {
            $this->command->warn('Недостаточно данных для заполнения цен (нужны сервисы и хотя бы одна организация/врач).');
            return;
        }

        for ($i = 1; $i <= 2000; $i++) {
            // Рандомно выбираем тип (например, 'App\Models\Clinic')
            $randomType = fake()->randomKey($targets);
            // Выбираем случайный ID именно для этого типа
            $randomId = fake()->randomElement($targets[$randomType]);

            DB::table('prices')->insert([
                'service_id'     => fake()->randomElement($serviceIds),
                'price'          => fake()->randomFloat(2, 300, 25000),
                'currency'       => '₽',  
                'priceable_id'   => $randomId,
                'priceable_type' => $randomType,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
        
        $this->command->info('Прайс-лист успешно заполнен для разных типов сущностей!');
    }
}