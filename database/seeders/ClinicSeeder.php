<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


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
                'description' => '',
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
                'description' => 'Ветлазарет — помощь питомцам 24/7. Стационар, хирургия, УЗИ, стоматология.',
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
    [
        'name' => 'Ветеринарная клиника Доктор Вет',
        'country' => 'Россия',
        'region' => 'Краснодарский край',
        'city' => 'Краснодар',
        'street' => 'Северная',
        'house' => '341',
        'address_comment' => '1 этаж жилого дома, отдельный вход',
        'logo' => '',
        'description' => 'Круглосуточная ветеринарная помощь, УЗИ, хирургия, стационар, вакцинация и выезд на дом.',
        'phone1' => '+7 (861) 238-88-88',
        'phone2' => '+7 (918) 400-00-00',
        'email' => 'info@doktorvet-krd.ru',
        'telegram' => 'doktorvet_krd',
        'whatsapp' => 'https://wa.me/79184000000',
        'website' => 'https://doktorvet-krd.ru',
        'schedule' => 'круглосуточно',
        'workdays' => 'Пн–Вс',
        'service_ids' => [1, 2, 3, 4, 5, 6, 7, 8],
    ],
    [
        'name' => 'Семейная ветеринарная клиника ЗооЛайн',
        'country' => 'Россия',
        'region' => 'Краснодарский край',
        'city' => 'Краснодар',
        'street' => 'им. Васнецова',
        'house' => '56',
        'address_comment' => 'Рядом с парком, отдельное здание',
        'logo' => '',
        'description' => 'Полный спектр услуг: терапия, дерматология, стоматология, лабораторная диагностика и груминг.',
        'phone1' => '+7 (861) 274-44-44',
        'phone2' => null,
        'email' => 'zooline-krd@yandex.ru',
        'telegram' => 'zooline_krd',
        'whatsapp' => 'https://wa.me/78612744444',
        'website' => 'https://zooline-krd.ru',
        'schedule' => 'с 9:00 до 20:00',
        'workdays' => 'Пн–Сб',
        'service_ids' => [1, 2, 5, 6, 7, 9],
    ],
    [
        'name' => 'Ветеринарная клиника Био-Вет',
        'country' => 'Россия',
        'region' => 'Краснодарский край',
        'city' => 'Краснодар',
        'street' => 'Уральская',
        'house' => '107/2',
        'address_comment' => 'Вход с торца, рядом с аптекой',
        'logo' => '',
        'description' => 'Специализированная диагностика, УЗИ, ЭКГ, анализы, вакцинация и профилактика паразитов.',
        'phone1' => '+7 (861) 292-22-92',
        'phone2' => '+7 (918) 292-22-92',
        'email' => 'biovetkrd@mail.ru',
        'telegram' => 'biovet_krd',
        'whatsapp' => 'https://wa.me/79182922292',
        'website' => 'https://biovet-krd.ru',
        'schedule' => 'с 8:00 до 20:00',
        'workdays' => 'Пн–Вс',
        'service_ids' => [1, 2, 5, 6, 8],
    ],
    [
        'name' => 'Клиника для животных ЗооМир',
        'country' => 'Россия',
        'region' => 'Краснодарский край',
        'city' => 'Краснодар',
        'street' => 'им. Героя Аверкиева',
        'house' => '15',
        'address_comment' => 'На первом этаже ТЦ "ЗооМир"',
        'logo' => '',
        'description' => 'Современное оборудование, опытные врачи, отделение неотложной помощи и косметология для животных.',
        'phone1' => '+7 (861) 237-77-77',
        'phone2' => null,
        'email' => 'zoomir-krd@info.ru',
        'telegram' => 'zoomir_krd',
        'whatsapp' => 'https://wa.me/78612377777',
        'website' => 'https://zoomir-krd.ru',
        'schedule' => 'с 9:00 до 21:00',
        'workdays' => 'Пн–Сб',
        'service_ids' => [1, 2, 3, 4, 5, 7],
    ],
    [
        'name' => 'Ветеринарная клиника Айболит',
        'country' => 'Россия',
        'region' => 'Краснодарский край',
        'city' => 'Краснодар',
        'street' => 'Красная',
        'house' => '147',
        'address_comment' => 'Центр города, рядом с универмагом',
        'logo' => '',
        'description' => 'Одна из старейших клиник Краснодара. Приём всех видов животных, включая экзотических.',
        'phone1' => '+7 (861) 262-22-22',
        'phone2' => '+7 (918) 262-22-22',
        'email' => 'aibolit-krd@yandex.ru',
        'telegram' => 'aibolit_krd',
        'whatsapp' => 'https://wa.me/79182622222',
        'website' => 'https://aibolit-krd.ru',
        'schedule' => 'с 8:30 до 20:00',
        'workdays' => 'Пн–Вс',
        'service_ids' => [1, 2, 5, 6, 7, 9, 10],
    ],
    [
        'name' => 'Центр Ветеринарной Медицины',
        'country' => 'Россия',
        'region' => 'Донецкая Народная республика',
        'city' => 'Донецк',
        'street' => 'пр-т Мира',
        'house' => '8',
        'address_comment' => '',
        'logo' => 'clinics/logo/dnr/cvm_dnr.png',
        'description' => 'Работает по предварительной записи',
        'phone1' => '+7 (949) 305-81-87',
        'phone2' => '+7(856) 345-03-15',
        'email' => 'cvm.dn@mail.ru',
        'telegram' => '',
        'whatsapp' => '',
        'website' => 'https://cvm-donetsk.ru/',
        'schedule' => 'с 8:00 до 16:00',
        'workdays' => 'Пн–Вс',
        'service_ids' => [1, 2, 5, 6, 7, 9, 10],
    ],
    [
        'name' => 'Динго Зооветеринарной центр',
        'country' => 'Россия',
        'region' => 'Донецкая Народная республика',
        'city' => 'Донецк',
        'street' => 'ул.50-летия СССР',
        'house' => '140',
        'address_comment' => '',
        'logo' => 'clinics/logo/dnr/dingo_dnr.png',
        'description' => 'Клиника в городе Донецк',
        'phone1' => '+7 (949) 333-25-83 ',
        'phone2' => '+7(856) 335-41-90',
        'email' => 'donvet@mail.ru',
        'telegram' => '',
        'whatsapp' => '',
        'website' => 'http://dingo-vet.com/index.php?id=17991&show=89399',
        'schedule' => 'с 9:00 до 17:00',
        'workdays' => 'Пн–Вс',
        'service_ids' => [1, 2, 5, 6, 7, 9, 10],
    ],
];


// Создаем клиники и прикрепляем услуги
foreach ($clinics as $data) {
    $serviceIds = $data['service_ids'];
    unset($data['service_ids']);

                // ✅ Генерация slug
            $data['slug'] = Str::slug(
                $data['name'] . ' ' . $data['city'] . ' ' . $data['street']
            );
    
    $clinic = Clinic::create($data);
    $clinic->services()->sync($serviceIds);
}
}
}