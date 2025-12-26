<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use App\Services\TelegramService;

class SendTelegramOnUserRegistered
{
    public function handle(Registered $event): void
    {
        $user = $event->user;

        $message = 
            "🎉 <b>Новая регистрация</b>\n\n" .
            "👤 Имя: {$user->name}\n" .
            "📧 Email: {$user->email}\n" .
            "🕒 Дата: " . now()->format('d.m.Y H:i');

        TelegramService::send($message);
    }
}
