<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;

class ClinicSeeder extends Seeder
{
    /**
     * Запуск сидера
     */
    public function run(): void
    {
        // Отключаем внешние ключи, чтобы безопасно очистить таблицы
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('clinics')->truncate();
        DB::table('clinic_service')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Основные клиники
        $clinics = [
            [
                'name' => 'Биосфера',
                'country' => 'Россия',
                'region' => 'Краснодарский край',
                'city' => 'Краснодар',
                'street' => 'ул. Горького',
                'house' => 'д.217',
                'address_comment' => 'Дмитровская Дамба',
                'logo' => 'clinics/logo/logo-biosfera.png',
                'description' => '            Далеко-далеко за словесными горами в стране, гласных и согласных живут рыбные тексты. Лучше даже взгляд вопрос рот от всех пустился моей семантика сбить рыбного свою парадигматическая деревни предложения строчка назад, ему подзаголовок единственное?',
                'phone1' => '+7 (861) 251-67-85',
                'phone2' => null,
                'email' => 'info@hvostvet.ru',
                'telegram' => 'dobrykhvost',
                'whatsapp' => null,
                'website' => 'https://biosfera.vet/',
                'schedule' => 'круглосуточно',
                'workdays' => 'Пн–Вс',
                'service_ids' => [1, 2, 3, 4],
            ],
            [
                'name' => 'Слон',
                'country' => 'Россия',
                'region' => 'Краснодарский край',
                'city' => 'Краснодар',
                'street' => 'Невский проспект',
                'house' => '105',
                'address_comment' => 'Вход со двора, 2 этаж',
                'logo' => 'clinics/logo/slon.svg',
                'description' => 'Современный ветеринарный центр с лабораторией, цифровым рентгеном и отделением реабилитации животных.',
                'phone1' => '+7 (812) 555-77-88',
                'phone2' => null,
                'email' => 'contact@lapaplus.ru',
                'telegram' => 'lapaplus_vet',
                'whatsapp' => 'https://wa.me/78125557788',
                'website' => null,
                'schedule' => 'с 9:00 до 21:00',
                'workdays' => 'Пн–Сб',
                'service_ids' => [2, 5, 7],
            ],
            [
                'name' => 'ВетЛазарет',
                'country' => 'Россия',
                'region' => 'Краснодарский край',
                'city' => 'Краснодар',
                'street' => 'Малышева',
                'house' => '56А',
                'address_comment' => 'Около ТЦ "Пассаж"',
                'logo' => 'clinics/logo/vetlazaret.png',
                'description' => 'Сеть клиник "Айболит" — помощь питомцам 24/7. Стационар, хирургия, УЗИ, стоматология.',
                'phone1' => '+7 (343) 333-22-11',
                'phone2' => null,
                'email' => 'aibolit24@vet.ru',
                'telegram' => 'aibolit_ekb',
                'whatsapp' => 'https://wa.me/73433332211',
                'website' => null,
                'schedule' => 'круглосуточно',
                'workdays' => 'Пн–Вс',
                'service_ids' => [1, 3, 6, 8],
            ],
        ];

        // + добавим 10 шаблонных клиник
        for ($i = 1; $i <= 10; $i++) {
            $clinics[] = [
                'name' => "Государственная Ветеринарная клиника №{$i}",
                'country' => 'Россия',
                'region' => 'Область №' . $i,
                'city' => 'Город  '. $i,
                'street' => 'Улица ' . $i,
                'house' => rand(1, 200),
                'address_comment' => '',
                'logo' => '',
                'description' => 'Типовая клиника с общим приёмом и терапией домашних животных.',
                'phone1' => '+7 (900) ' . rand(100, 999) . '-' . rand(10, 99) . '-' . rand(10, 99),
                'phone2' => null,
                'email' => 'clinic' . $i . '@mail.ru',
                'telegram' => null,
                'whatsapp' => null,
                'website' => null,
                'schedule' => 'с 9:00 до 20:00',
                'workdays' => 'Пн–Сб',
                'service_ids' => [1, 2],
            ];
        }

        // Создаем клиники и прикрепляем услуги
        foreach ($clinics as $data) {
            $serviceIds = $data['service_ids'];
            unset($data['service_ids']);

            $clinic = Clinic::create($data);
            $clinic->services()->sync($serviceIds);
        }
    }
}
