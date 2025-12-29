<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FieldOfActivity;

class FieldOfActivitySeeder extends Seeder
{
    public function run(): void
    {
        // 🧑‍⚕️ Ветеринарные врачи (одна сфера — doctor)
        $doctors = [
            'Хирург',
            'Терапевт',
            'Травматолог',
            'Гастроэнтеролог',
            'Нефролог',
            'Радиолог',
            'Офтальмолог',
            'Анестезиолог',
            'Иммунолог',
            'Аллерголог',
            'Онколог',
            'Невролог',
            'Дерматолог',
            'Эндокринолог',
            'Кардиолог',
            'Реабилитолог',
            'Реаниматолог',
            'Репродуктолог',
            'Стоматолог',
            'Ортопед',
            'Экзотолог',
            'Вет диетолог',
        ];

        foreach ($doctors as $name) {
            FieldOfActivity::create([
                'name'     => $name,
                'type'     => 'specialist',
                'activity' => 'doctor',
            ]);
        }

        // 🧑‍🏫 Специалисты других сфер
        $specialists = [
            'Грумер'             => 'grooming',
            'Кинолог'            => 'cynology',
            'Тренер по аджилити' => 'cynology',
            'Зоопсихолог'        => 'behavior',
            'Фелинолог'          => 'felinology',
            'Заводчик'           => 'breeding',
            'Зооняня'            => 'hotel',
        ];

        foreach ($specialists as $name => $activity) {
            FieldOfActivity::create([
                'name'     => $name,
                'type'     => 'specialist',
                'activity' => $activity,
            ]);
        }

        // 🏢 Организации
        $organizations = [
            'Ветеринарная клиника'  => 'doctor',
            'Зооцентр'              => 'doctor',
            'Груминг салон'         => 'grooming',
            'Кинологический центр'  => 'cynology',
            'Фелинологический клуб' => 'felinology',
            'Питомник'              => 'breeding',
            'Зоогостиница'          => 'hotel',
        ];

        foreach ($organizations as $name => $activity) {
            FieldOfActivity::create([
                'name'     => $name,
                'type'     => 'organization',
                'activity' => $activity,
            ]);
        }
    }
}
