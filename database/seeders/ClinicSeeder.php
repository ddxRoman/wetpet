<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinic;

class ClinicSeeder extends Seeder
{
    /**
     * Запуск сидера
     */
    public function run(): void
    {
        Clinic::truncate(); // Очистим таблицу перед вставкой (необязательно)

        $clinics = [
            [
                'name' => 'ВетКлиника "Добрый Хвост"',
                'country' => 'Россия',
                'region' => 'Московская область',
                'city' => 'Москва',
                'street' => 'Ленинградский проспект',
                'house' => '25к1',
                'address_comment' => 'Рядом с метро "Динамо"',
                'logo' => '/images/clinics/dobrykhvost.png',
                'description' => 'Полный спектр ветеринарных услуг: терапия, хирургия, вакцинация, анализы и груминг.',
                'services' => [1, 2, 3, 4],
                'doctors' => [1, 2],
                'phone1' => '+7 (495) 123-45-67',
                'phone2' => '+7 (915) 654-32-10',
                'email' => 'info@hvostvet.ru',
                'telegram' => '@dobrykhvost',
                'whatsapp' => 'https://wa.me/79156543210',
                'schedule' => 'с 8:00 до 22:00',
                'workdays' => 'Пн–Вс',
            ],
            [
                'name' => 'Зоомед Центр "Лапа+”',
                'country' => 'Россия',
                'region' => 'Санкт-Петербург',
                'city' => 'Санкт-Петербург',
                'street' => 'Невский проспект',
                'house' => '105',
                'address_comment' => 'Вход со двора, 2 этаж',
                'logo' => '/images/clinics/lapaplus.png',
                'description' => 'Современный ветеринарный центр с лабораторией, цифровым рентгеном и отделением реабилитации животных.',
                'services' => [2, 5, 7],
                'doctors' => [3, 4, 5],
                'phone1' => '+7 (812) 555-77-88',
                'phone2' => null,
                'email' => 'contact@lapaplus.ru',
                'telegram' => '@lapaplus_vet',
                'whatsapp' => 'https://wa.me/78125557788',
                'schedule' => 'с 9:00 до 21:00',
                'workdays' => 'Пн–Сб',
            ],
            [
                'name' => 'Ветеринарная клиника "Айболит"',
                'country' => 'Россия',
                'region' => 'Свердловская область',
                'city' => 'Екатеринбург',
                'street' => 'Малышева',
                'house' => '56А',
                'address_comment' => 'Около ТЦ "Пассаж"',
                'logo' => '/images/clinics/aibolit.png',
                'description' => 'Сеть клиник "Айболит" — помощь питомцам 24/7. Стационар, хирургия, УЗИ, стоматология.',
                'services' => [1, 3, 6, 8],
                'doctors' => [6, 7],
                'phone1' => '+7 (343) 333-22-11',
                'phone2' => null,
                'email' => 'aibolit24@vet.ru',
                'telegram' => '@aibolit_ekb',
                'whatsapp' => 'https://wa.me/73433332211',
                'schedule' => 'круглосуточно',
                'workdays' => 'Пн–Вс',
            ],
        ];

        foreach ($clinics as $clinic) {
            Clinic::create($clinic);
        }
    }
}
