<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;

class TestController extends Controller
{
    
    public function test()
    {

        TelegramService::send(
            "🎉 <b>Тестовое сообщение</b>\n\n" .
            "👤 Имя: Тестовый пользователь\n" .
            "📧 Email: test@test.ru\n" .
            "📱 Телефон: 123456789\n" .
            "🕒 Дата: " . now()->format('d.m.Y H:i')
        );

        return 'OK';
    }

    
}
