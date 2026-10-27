<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Предупреждение о предстоящем автосписании за несколько дней до окончания подписки.
 */
class AutoRenewalUpcoming extends Notification
{
    public function __construct(
        public string $title,
        public ?int $amount,
        public string $chargeDate,
        public ?string $card,
        public string $manageUrl
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    private function text(): string
    {
        $sum  = $this->amount !== null ? " {$this->amount} ₽" : '';
        $card = $this->card ? " ({$this->card})" : '';

        return "Около {$this->chargeDate} мы автоматически спишем{$sum} за продление подписки «{$this->title}» с сохранённого способа оплаты{$card}.";
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Скоро продление подписки — Зверозор')
            ->line($this->text())
            ->line('Если вы не хотите продлевать подписку, отключите её в личном кабинете до этой даты — она продолжит действовать до конца оплаченного срока.')
            ->action('Управление подпиской', $this->manageUrl);
    }

    public function toArray($notifiable): array
    {
        return [
            'title'   => $this->title,
            'message' => $this->text(),
            'url'     => $this->manageUrl,
        ];
    }
}
