<?php

return [

    /*
    | Платёжный шлюз. Пока платёжная система не выбрана:
    |  - в локальном окружении (APP_ENV=local) работает тестовый шлюз 'fake'
    |    (кнопка «Оплатить» на внутренней странице, деньги не списываются);
    |  - в остальных окружениях оплата отключена, пока не задан PREMIUM_GATEWAY.
    | Для ЮKassa: PREMIUM_GATEWAY=yookassa + YOOKASSA_SHOP_ID / YOOKASSA_SECRET_KEY в .env.
    | Новый шлюз — добавьте класс и зарегистрируйте его в
    | App\Services\Premium\PremiumService::gateway() и App\Services\Boost\BoostService::gateway().
    */
    'gateway' => env('PREMIUM_GATEWAY', env('APP_ENV') === 'local' ? 'fake' : null),

    // Тарифы: цена в рублях, срок в месяцах
    'plans' => [
        'month' => ['label' => '1 месяц', 'price' => 70,  'months' => 1],
        'year'  => ['label' => '1 год',   'price' => 600, 'months' => 12],
    ],

    /*
    | Автопродление (ЮKassa, автоплатежи). Применяется и к Премиуму, и к пакетам продвижения.
    | Списание — за charge_before_hours до окончания; при отказе банка повторяем не чаще
    | чем раз в retry_after_hours, всего max_attempts попыток, затем автопродление отключается.
    */
    'auto_renew' => [
        'charge_before_hours' => 24,
        'retry_after_hours'   => 20,
        'max_attempts'        => 3,
    ],

    // Лимиты фотографий: free — обычный пользователь, premium — с подпиской
    'limits' => [
        'pet_photos'    => ['free' => 1, 'premium' => 10], // всего на питомца (с основным фото)
        'review_photos' => ['free' => 1, 'premium' => 5], // на один отзыв
    ],
];
