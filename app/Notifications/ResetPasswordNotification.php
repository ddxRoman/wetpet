<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Сброс пароля')
            ->line('Вы получили это письмо, потому что запросили сброс пароля.')
            ->action('Сбросить пароль', $url)
            ->line('Если вы не запрашивали сброс, просто проигнорируйте это письмо.');
    }
}
