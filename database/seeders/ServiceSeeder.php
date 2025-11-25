<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Базовый список услуг
        $baseServices = [
            ['name' => 'Общий осмотр', 'spec' => 'Терапия', 'doc' => 'Терапевт'],
            ['name' => 'Вакцинация', 'spec' => 'Терапия', 'doc' => 'Терапевт'],
            ['name' => 'Лечение простудных заболеваний', 'spec' => 'Терапия', 'doc' => 'Терапевт'],
            ['name' => 'УЗИ диагностика', 'spec' => 'Диагностика', 'doc' => 'Диагност'],
            ['name' => 'Рентген', 'spec' => 'Диагностика', 'doc' => 'Диагност'],
            ['name' => 'Стерилизация', 'spec' => 'Хирургия', 'doc' => 'Хирург'],
            ['name' => 'Кастрация', 'spec' => 'Хирургия', 'doc' => 'Хирург'],
            ['name' => 'Удаление зубов', 'spec' => 'Стоматология', 'doc' => 'Стоматолог'],
            ['name' => 'Груминг', 'spec' => 'Уход', 'doc' => 'Грумер'],
            ['name' => 'Проверка зрения', 'spec' => 'Офтальмология', 'doc' => 'Офтальмолог'],
            ['name' => 'Терапия кожи', 'spec' => 'Дерматология', 'doc' => 'Дерматолог'],
            ['name' => 'Диагностика судорожных состояний', 'spec' => 'Неврология', 'doc' => 'Невролог'],
            ['name' => 'Ведение беременности', 'spec' => 'Репродуктология', 'doc' => 'Репродуктолог'],
        ];

        $count = 0;

        // Генерируем 500 записей
        while ($count < 500) {
            foreach ($baseServices as $service) {

                if ($count >= 500) break;

                // Добавим разные варианты имени
                $suffix = rand(1000, 9999);
                $serviceName = $service['name'] . " #$suffix";

                Service::create([
                    'name'                  => $serviceName,
                    'specialization'        => $service['spec'],
                    'specialization_doctor' => $service['doc'],
                ]);

                $count++;
            }
        }
    }
}
