<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Не удалось автопродлить подписку (отказ банка и т.п.).
 */
class AutoRenewalFailed extends Notification
{
    public function __construct(
        public string $title,
        public bool $disabled,
        public ?string $reason = null
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    private function text(): string
    {
        return $this->disabled
            ? "Не удалось списать оплату за «{$this->title}», подписка больше не будет продлеваться автоматически. Она будет действовать до конца оплаченного срока — оформите подписку заново."
            : "Не удалось списать оплату за «{$this->title}». Мы попробуем ещё раз в ближайшие дни — проверьте, что на карте достаточно средств.";
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Не удалось продлить подписку — Зверозор')
            ->line($this->text())
            ->action('Перейти в личный кабинет', url('/'));
    }

    public function toArray($notifiable): array
    {
        return [
            'title'   => $this->title,
            'message' => $this->text(),
        ];
    }
}
