<?php

namespace App\Notifications;

use App\Models\Award;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AwardApproved extends Notification
{
    use Queueable;

    public function __construct(public Award $award)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Ваша награда одобрена!')
            ->line("Награда: {$this->award->title}")
            ->line('Ваше достижение было одобрено администратором.')
            ->action('Перейти в личный кабинет', url('/'))
            ->line('Поздравляем!');
    }

    public function toArray($notifiable): array
    {
        return [
            'award_id' => $this->award->id,
            'title' => $this->award->title,
            'message' => 'Ваша награда была подтверждена.',
        ];
    }
}
