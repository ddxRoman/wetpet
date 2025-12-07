<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FieldOfActivity;

class FieldOfActivitySeeder extends Seeder
{
    public function run()
    {
        $specialists = [
            'Ветеринар',
            'Хирург',
            'Терапевт',
            'Офтальмолог',
            'Дерматолог',
            'Кардиолог',
            'Стоматолог',
            'Ортопед',
            'Грумер',
            'Дрессировщик',
            'Фелинолог',
            'Кинолог',
            'Экзотолог',
            'Реабилитолог'
        ];

        $organizations = [
            'Ветеринарная клиника',
            'Зооцентр',
            'Собачья площадка',
            'Грумерский центр',
            'Ветеринарный сад',
            'Зоомагазин',
            'Питомник',
            'Хоспис для животных',
            'Отель для животных',
            'Зоосалон',
            'Приют',
        ];

        foreach ($specialists as $name) {
            FieldOfActivity::create([
                'name' => $name,
                'type' => 'specialist',
                'activity' => 'doctor'
            ]);
        }

        foreach ($organizations as $name) {
            FieldOfActivity::create([
                'name' => $name,
                'type' => 'organization',
                'activity' => 'Vetclinic'
            ]);
        }
    }
}
