<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'yookassa' => [
        'shop_id'    => env('YOOKASSA_SHOP_ID'),
        'secret_key' => env('YOOKASSA_SECRET_KEY'),
        // Автоплатежи (сохранение способа оплаты). Включайте только после того, как менеджер ЮKassa
        // подключил их для магазина, иначе ЮKassa отвечает 403 «This store can't make recurring payments».
        'recurring'  => env('YOOKASSA_RECURRING', false),
    ],

// Телеграммм
    'telegram' => [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'chat_id'  => env('TELEGRAM_CHAT_ID'),
],



];
